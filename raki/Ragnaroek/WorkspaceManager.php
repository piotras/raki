<?php

class RagnaroekWorkspaceManager implements WorkspaceManager 
{
    private $default_sg_zero = 'SG0';
    private $default_language;
    private $sitegroups = array();
    private $languages = array();
    private $mgd = null;

    public function __construct($default_language = null)
    {
        $this->mgd = MidgardConnection::get_instance();
        if ($default_language == null) {
            $this->default_language = new midgard_language();
            $this->default_language->code = 'multilang';
        } else {
            $this->default_language = $this->getLangByCode($default_language);
        }
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

        return $sgs; 
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

    public function getPossibleWorkspacesPaths()
    {
        $names = $this->getPossibleWorkspacesNames();
        $paths = array();

        self::buildWorkspacesPaths($paths, $names);       

        return $paths;
    }

    public function createWorkspace($name, $parent = null)
    {

    }
}

?>
