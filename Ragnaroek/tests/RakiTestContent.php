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
        foreach ($re->getClasses() as $refclass) {

            if ($refclass->isAbstract() || $refclass->isInterface()) {
                continue;
            }

            $name = $refclass->getName();
            if (!is_subclass_of($name, 'MidgardDBObject')) {
                continue;
            }

            /* Ugly hack for pseudo abstract classes */
            if (strrpos($name, "_abstract") != false) {
                continue;
            }

            //echo 'midgard_storage: create_class_storage('.$name.")\n";
            echo ".";
            if (true !== midgard_storage::create_class_storage($name)) {
                throw new Exception('Failed to create storage for "'.$name.': "'.midgard_connection::get_instance()->get_error_string());
            }

            if (true !== midgard_storage::update_class_storage($name)) {
                #throw new Exception('Failed to update storage for "'.$name.': "'.midgard_connection::get_instance()->get_error_string());
            }
        }

        echo " OK. Done. \n";
    }

    public static function importLanguages()
    {   
        /* Import languages */
        $filepath = __DIR__ . "/../data/midgard_languages.xml";
        $xml = file_get_contents($filepath);
        $rv = MidgardReplicator::import_from_xml($xml);
    }

    public static function createLangContent($typeName, $title = '', $sID, $sgID, $lang)
    {
        $o = new $typeName();
        $o->title = $title;
        $o->sid = $sID;
        $o->sitegroup = $sgID;
        $o->lang = RakiTestHelper::getLangByCode($lang)->id;
        if ($o->create() == false) {
            throw new \Exception("Failed to create {$typeName}. " . midgard_connection::get_instance()->get_error_string() );
        }
    }

    public static function createTypeRecord($typeName, $name, $sgID) 
    {
        $o = new $typeName();
        $o->name = $name;
        $o->sitegroup = $sgID;
        if ($o->create() == false) {
            throw new \Exception("Failed to create {$typeName}. " . midgard_connection::get_instance()->get_error_string() );
        }
        return $o;
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

        /* TOPIC */ 
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
        self::createLangContent('ragnaroek_topic_lang', RakiTestHelper::LangFiName, $t->id, $sg->id, 'fi');
        // RU
        self::createLangContent('ragnaroek_topic_lang', RakiTestHelper::LangRuName, $t->id, $sg->id, 'ru');


        /* STYLE ELEMENT */
        $e = self::createTypeRecord('ragnaroek_element', RakiTestHelper::SG1TopicName, $sg->id);
        
        // FI
        self::createLangContent('ragnaroek_element_lang', RakiTestHelper::LangFiName, $e->id, $sg->id, 'fi');
        // RU
        self::createLangContent('ragnaroek_element_lang', RakiTestHelper::LangRuName, $e->id, $sg->id, 'ru');


        /* PAGE */
        $p = self::createTypeRecord('ragnaroek_page', RakiTestHelper::SG1TopicName, $sg->id);
        
        // FI
        self::createLangContent('ragnaroek_page_lang', RakiTestHelper::LangFiName, $p->id, $sg->id, 'fi');
        // RU
        self::createLangContent('ragnaroek_page_lang', RakiTestHelper::LangRuName, $p->id, $sg->id, 'ru');
    }

    public static function prepareContent()
    {
        self::createStorage();
        self::importLanguages();
        self::createContent();
    }
}

?>
