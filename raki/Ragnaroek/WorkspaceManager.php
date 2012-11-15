<?php

class RagnaroekWorkspaceManager implements WorkspaceManager 
{
    private $default_sg_zero = 'SG0';
    private $default_language;
    private $default_sitegroup = null;
    private $sitegroups = array();
    private $languages = array();
    private $mgd = null;
    private $midgardWorkspaceManager = null;
    private $workspaces = array();
    private $possible_workspaces = null;
    private $possible_paths= null;
    private $transition;

    public function __construct($transition, $default_language = null)
    {
        $this->mgd = MidgardConnection::get_instance();
        if ($default_language == null) {
            $this->default_language = new midgard_language();
            $this->default_language->code = 'multilang';
        } else {
            $this->default_language = $this->getLangByCode($default_language);
        }
        $this->default_sitegroup = new ragnaroek_sitegroup();
        $this->transition = $transition;
    }

    public function getDefaultWorkspaceName()
    {
        return $this->default_sg_zero;
    }

    public function getTransition()
    {
        return $this->transition;
    }

    public function getLangByCode($code) 
    {
        $storage = new MidgardQueryStorage('midgard_language');
        $qs = new MidgardQuerySelect($storage);
        $qs->set_constraint(
            new midgard_query_constraint(
                new midgard_query_property('code'),
                '=',
                new midgard_query_value($code)
            )
        );
        $qs->execute();
        if ($qs->get_results_count() != 1) {
            throw new Exception("Failed to get language by '{$code}' code");
        }

        $langs = $qs->list_objects();
        return $langs[0];
    }

    public function getStorableTypeNames()
    {
        $re = new ReflectionExtension("midgard2");
        $names = array();
        foreach ($re->getClasses() as $class_ref) {
            $class_mgd_ref = new midgard_reflection_class($class_ref->getName());
            $name = $class_mgd_ref->getName();
            if (!is_subclass_of ($name, 'MidgardDBObject')
                || $class_mgd_ref->isAbstract()) {
                    continue;
                }

            $names[] = $name; 
        }

        return $names;
    }

    private function populateSitegroups()
    {
        $storage = new MidgardQueryStorage("ragnaroek_sitegroup");
        $qs = new MidgardQuerySelect($storage);
        $qs->execute();

        if ($qs->get_results_count() < 1) {
            return;
        }

        foreach ($qs->list_objects() as $sg) {
            $this->sitegroups[] = $sg;
        }
    }

    private function getTypeLanguages($type)
    {
        $column_name = "language";
        $qsd = new MidgardSqlQuerySelectData($this->mgd); 
        $storage = new MidgardQueryStorage($type);
        $column = new MidgardSqlQueryColumn(
            new MidgardQueryProperty('lang', $storage),
            'multilang_type',
            $column_name
        );
        $qsd->add_column($column);
        $qsd->execute();
        $rows = $qsd->get_query_result()->get_rows();
        
        if (empty($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $lang = $row->get_value($column_name);
            if ($lang == $this->default_language->id) {
                continue;
            }
            $this->languages[] = new midgard_language($lang);
        }
    }

    private function populateLanguages()
    {
        $types = $this->getStorableTypeNames();

        /* Determine multilang type.
         * True if 'sid' and 'lang' properties exist */
        foreach ($types as $type) {
            /* Workaround for missed "default properties" in php class */
            $tmp = new $type;
            if (!property_exists($tmp, 'sid')
                && !property_exists($tmp,'lang')) {
                    continue;    
                }
            $mlTypes[] = $type;
        }

        /* Select language(s) id from every available multilang type */
       
        foreach ($mlTypes as $type) {
            $this->getTypeLanguages($type);
        }
    }

    public function getPossibleWorkspacesNames($parent = null)
    {
        if ($this->possible_workspaces != null) {
            return $this->possible_workspaces;
        }

        /* Get all sitegroups. Those are used as "base" workspaces */
        $this->populateSitegroups();

        /* Get all languages, those are child workspaces of sitegroups */
        $this->populateLanguages();

        $sgs[$this->default_sg_zero] = array();
        foreach ($this->sitegroups as $sg) {
            $sgs[$this->default_sg_zero][$sg->name] = array();
            if (!empty($this->languages)) {
                foreach ($this->languages as $lang) {
                    $sgs[$this->default_sg_zero][$sg->name][$this->default_language->code][$lang->code] = array();
                }
            }
        }       

        $this->possible_workspaces = $sgs;
        return $this->possible_workspaces;
    }

    private function buildWorkspacesPaths(&$paths, $elements, $base = '')
    {
        foreach ($elements as $name => $sgs) {
            $paths[] = $base . '/' . $name;
            foreach ($sgs as $lname => $lgs) {
                $path = '/' . $name . '/' . $lname;
                $paths[] = $base . $path;      
                self::buildWorkspacesPaths($paths, $lgs, $path);
            }
        }
    }

    private function populatePaths()
    {
        if (!empty($this->possible_paths)) {
            return;
        }

        $names = $this->getPossibleWorkspacesNames();
        $this->possible_paths = array(); 

        self::buildWorkspacesPaths($this->possible_paths, $names);       
    }

    public function getPossibleWorkspacesPaths()
    {
        $this->populatePaths();
        return $this->possible_paths;
    }

    private function getMidgardWorkspaceManager()
    {
        if ($this->midgardWorkspaceManager == null) {
            $this->midgardWorkspaceManager = new midgard_workspace_manager($this->mgd);
        }
        return $this->midgardWorkspaceManager;
    }

    private function findSitegroupByName($name)
    {
        foreach ($this->sitegroups as $sg) {
            if ($sg->name == $name) {
                return $sg;
            }
        }

        return null;
    }

    private function findLanguageByName($name)
    {
        if ($this->default_language->code == $name) {
            return $this->default_language;
        }

        foreach ($this->languages as $lang) {
            if ($lang->code == $name) { 
                return $lang;
            }
        }

        return null;
    }

    private function determineLegacyType($absPath)
    {
        $elements = explode('/', $absPath);
        $elements_count = count($elements);

        switch ($elements_count) {
            
        case 1:
            throw new Exception("Invalid absolute path to determine legacy Midgard type");

        case 2:
            return $this->default_sitegroup;

        case 3:
            $sg = $this->findSitegroupByName($elements[2]);
            if ($sg != null) {
                return $sg;
            }
            break;
        }

        return $this->findLanguageByName($elements[$elements_count - 1]);
    }   

    public function createWorkspace($name, StorableWorkspace $parent = null)
    {
        $parent ? $parent_path = $parent->getPath() : $parent_path = '';
        $absPath = $parent_path . '/' . $name;

        $this->populatePaths();

        if ($this->possible_paths == null
            || in_array($absPath, $this->possible_paths) == false) {
                throw new Exception ("Can not create workspace at '{$absPath}' path. Not defined in possible workspaces");
            }

        $ws = new midgard_workspace();
        $ws->name = $name;
        $this->getMidgardWorkspaceManager()->create_workspace($ws, $parent_path); 

        /* Hold workspaces paths and associated sitegroup or language */
        $absPath = $parent_path . '/' . $name;
        $this->workspaces[$absPath]['workspace'] = $ws;
        $this->workspaces[$absPath]['legacy'] = $this->determineLegacyType($absPath);
    }

    public function getLegacyMidgardType($absPath) 
    {
        $this->populateSitegroups();
        $this->populateLanguages();

        return $this->determineLegacyType($absPath);
    }

    public function getMidgardWorkspaceByPath($absPath)
    {
        $ws = new MidgardWorkspace();
        try {
            $this->getMidgardWorkspaceManager()->get_workspace_by_path($ws, $absPath);
        } catch (Exception $e) {
            throw new Exception($e->getMessage() . ". " . $absPath);
        }
        return $ws;
    }

    public function createWorkspacesAll()
    {
        $paths = $this->getPossibleWorkspacesPaths();
        foreach ($paths as $path) {
            $elements = explode ('/', $path);
            $elements_count = count($elements);
            try {
                $name = $elements[$elements_count - 1];
                $parent = $elements[$elements_count - 2];
                if ($parent == '') {
                    $parent = null;
                } else {
                    $parent_path = $elements;
                    unset($parent_path[$elements_count - 1]);
                    $parent_path = implode('/', $parent_path);
                    $parent = $this->getStoredWorkspaceByPath($parent_path);
                }
                $this->createWorkspace($name, $parent);
            } catch (Exception $e) {
                if ($e->getMessage() != "Failed to create workspace. WorkspaceStorage at path '{$path}' already exists") {
                    throw $e;
                }
            }
        }
    }

    private function getChildrenNames($ws, &$names)
    {
        $children = $ws->list_children();

        if (empty($children)) {
            return;
        }

        foreach ($children as $child) {
            $names[$child->name] = array();
            $this->getChildrenNames($child, $names[$child->name]);
        }
    }

    public function getStoredWorkspacesNames()
    {
        $names = array();
        $ws = new MidgardWorkspace();
        $this->getMidgardWorkspaceManager()->get_workspace_by_path($ws, '/' . $this->default_sg_zero);
        $names[$this->default_sg_zero] = array();

        $this->getChildrenNames($ws, $names[$this->default_sg_zero]);

        return $names;
    }

    public function getStoredWorkspacesPaths()
    {
        $names = $this->getStoredWorkspacesNames();
        $paths = array();
        self::buildWorkspacesPaths($paths, $names);
        return $paths;
    }

    public function getStoredWorkspaceByPath($absPath)
    {
        $ws = new MidgardWorkspace();
        if (!$this->getMidgardWorkspaceManager()->get_workspace_by_path($ws, $absPath)) {
            throw new WorkspaceNotFoundException("Workspace doesn't exist at '{$absPath}' path");
        }

        return new RagnaroekStorableWorkspace($ws);
    }

    public function getStoredWorkspaceByName($name)
    {
        /* TODO, Throw exception in case of ambigious, duplicate names */
        $paths = $this->getStoredWorkspacesPaths();
        foreach ($paths as $path) {
            $elements = explode('/', $path);
            if ($elements[count($elements)-1] === $name) {
                return $this->getStoredWorkspaceByPath($path);
            }
        }

        throw new WorkspaceNotFoundException("Workspace identified by '{$name}' doesn't exist");
    }

    public function getDefaultLanguage()
    {
        return $this->default_language;
    }

    public function getMidgardSitegroups()
    {
        $this->populateSitegroups();
        return $this->sitegroups;
    }

    public function getMidgardLanguagesByType($sts, $typeName)
    {
        $languages = array();

        $table = $sts->getTableByType($typeName) . "_i";
        $sql = "SELECT DISTINCT lang FROM {$table} \n";
        $mysql = $this->getTransition()->getMySQL();

        $mysql->query($sql);
        while (($result = $mysql->getQueryResult()) != null) {
            $languages[] = $result['lang'];
        } 

        return $languages;
    }

    public function storedWorkspacePathExists($absPath)
    {
        return $this->getMidgardWorkspaceManager()->path_exists($absPath);
    }
}

?>
