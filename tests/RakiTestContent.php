<?php

require 'RakiTestHelper.php';

class RakiTestContent
{
    public static function createStorage()
    {
    
        /* Create storage for registered types */
        echo "Create Midgard storage. Please wait...";
        midgard_storage::create_base_storage();
        
        $re = new ReflectionExtension("midgard2");
        foreach ($re->getClasses() as $class_ref) {
            $class_mgd_ref = new midgard_reflection_class($class_ref->getName());
            $parent_class = $class_mgd_ref->getParentClass();
        
            $name = $class_mgd_ref->getName();
            if (!is_subclass_of ($name, 'MidgardDBObject')
                || $class_mgd_ref->isAbstract()) {
                    continue;
                }
        
            //echo 'midgard_storage: create_class_storage('.$name.")\n";
            echo ".";
            if (true !== midgard_storage::create_class_storage($name)) {
                throw new Exception('Failed to create storage for "'.$name.': "'.midgard_connection::get_instance()->get_error_string());
            }
        }

        echo " OK. Done. \n";
    }

    public static function importLanguages()
    {   
        /* Import languages */
        $filepath = "data/midgard_languages.xml";
        $xml = file_get_contents($filepath);
        $rv = MidgardReplicator::import_from_xml($xml);
    }

    public static function createContent()
    {
        
        /* Create SG0 content */
        $t = new ragnaroek_topic();
        $t->name = RakiTestHelper::SG0TopicName;
        $t->create();
        
        /* Create new sitegroup */
        $sg = new ragnaroek_sitegroup();
        $sg->name = RakiTestHelper::SG1Name;
        $sg->create();
        
        /* Create content for default language */
        $t = new ragnaroek_topic();
        $t->name = RakiTestHelper::SG1TopicName;
        $t->sitegroup = $sg->id;
        $t->create();
        
        $tml = new ragnaroek_topic_lang();
        $tml->title = RakiTestHelper::LangEnName;
        $tml->sid = $t->id;
        $tml->sitegroup = $sg->id;
        $tml->create();
        
        /* Create content for languages */
        
        // FI
        $tml = new ragnaroek_topic_lang();
        $tml->title = RakiTestHelper::LangFiName;
        $tml->sid = $t->id;
        $tml->sitegroup = $sg->id;
        $tml->lang = RakiTestHelper::getLangByCode('fi')->id; 
        $tml->create();

        // RU
        $tml = new ragnaroek_topic_lang();
        $tml->title = RakiTestHelper::LangRuName;
        $tml->sid = $t->id;
        $tml->sitegroup = $sg->id;
        $tml->lang = RakiTestHelper::getLangByCode('ru')->id;
        $tml->create();
    }

    public static function prepareContent()
    {
        self::createStorage();
        self::importLanguages();
        self::createContent();
    }
}

?>
