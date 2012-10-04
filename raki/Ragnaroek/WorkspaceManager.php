<?php

class RagnaroekWorkspaceManager implements WorkspaceManager 
{
    private $default_sg_zero = 'SG0';
    private $default_language = '';
    private $sitegroups = array();

    public function __construct($default_language = '')
    {

    }

    private function getSitegroups()
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

    public function getTypeLanguages($type)
    {
        
    }

    public function getPossibleWorkspacesNames($parent = null)
    {
        $this->getSitegroups();

        $sgs[$this->default_sg_zero] = array();
        foreach ($this->sitegroups as $sg) {
            $sgs[$this->default_sg_zero][] = $sg->name;
        }       

        return $sgs; 
    }
    
    public function dumpWorkspaces()
    {

    }

    public function createWorkspace($name, $parent = null)
    {

    }
}

?>
