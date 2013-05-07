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

    public static function createTypeRecord($typeName, $properties) 
    {
        $o = new $typeName();
        foreach ($properties as $name => $value) {
            if (property_exists($o, $name)) {
                $o->$name = $value;
            }
        }
        if ($o->create() == false) {
            throw new \Exception("Failed to create {$typeName}. " . midgard_connection::get_instance()->get_error_string() );
        }
        return $o;
    }

    public static function createContent()
    {  
        $properties['name'] = RakiTestHelper::SG0TopicName; 

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


        /* Create content for languages */
        // EN
        $properties['title'] = RakiTestHelper::LangEnName;
        $properties['sid'] = $t->id;
        $properties['sitegroup'] = $sg->id;
        $properties['lang'] = 0;
        self::createTypeRecord('ragnaroek_topic_lang', $properties);
        // FI
        $properties['title'] = RakiTestHelper::LangFiName;
        $properties['lang'] = RakiTestHelper::getLangByCode('fi')->id;
        self::createTypeRecord('ragnaroek_topic_lang', $properties);
        // RU
        $properties['title'] = RakiTestHelper::LangRuName;
        $properties['lang'] = RakiTestHelper::getLangByCode('ru')->id;
        self::createTypeRecord('ragnaroek_topic_lang', $properties); 


        /* STYLE ELEMENT */
        $properties['name'] = RakiTestHelper::SG1TopicName;
        $properties['style'] = 777;
        $e = self::createTypeRecord('ragnaroek_element', $properties); 
        
        // FI
        $properties['name'] = RakiTestHelper::LangFiName;
        $properties['sid'] = $e->id;
        $properties['lang'] = RakiTestHelper::getLangByCode('fi')->id;
        $properties['value'] = 'Suomi';
        self::createTypeRecord('ragnaroek_element_lang', $properties); 
        // RU
        $properties['name'] = RakiTestHelper::LangRuName;
        $properties['lang'] = RakiTestHelper::getLangByCode('ru')->id;
        $properties['value'] = 'Русский';
        self::createTypeRecord('ragnaroek_element_lang', $properties); 


        /* PAGE */
        $properties['name'] = RakiTestHelper::SG1TopicName;
        $properties['lang'] = 0;
        $properties['style'] = 777;
        $properties['up'] = 0;
        $properties['component'] = 'fa.fi.fu';
        $p = self::createTypeRecord('ragnaroek_page', $properties); 
        
        // FI
        $properties['title'] = RakiTestHelper::LangFiName;
        $properties['sid'] = $p->id;
        $properties['lang'] = RakiTestHelper::getLangByCode('fi')->id;
        $properties['content'] = "Lorem Ipsum";
        self::createTypeRecord('ragnaroek_page_lang', $properties);
        // RU
        $properties['title'] = RakiTestHelper::LangRuName;
        $properties['lang'] = RakiTestHelper::getLangByCode('ru')->id;
        self::createTypeRecord('ragnaroek_page_lang', $properties); 


        /* HOST */
        $properties['name'] = 'www.host.com';
        $properties['lang'] = 0;
        $properties['style'] = 777;
        $properties['port'] = 80;
        $properties['online'] = 1;
        $properties['prefix'] = '/en';
        $p = self::createTypeRecord('ragnaroek_host', $properties);


        /* STYLE */
        $properties['name'] = 'Stylish';
        $properties['owner'] = 123;
        $properties['up'] = 0;
        $p = self::createTypeRecord('ragnaroek_style', $properties);

        /* PAGE ELEMENT */
        $properties['name'] = RakiTestHelper::SG1TopicName;
        $properties['page'] = 101;
        $e = self::createTypeRecord('ragnaroek_pageelement', $properties); 
        
        // FI
        $properties['name'] = RakiTestHelper::LangFiName;
        $properties['sid'] = $e->id;
        $properties['lang'] = RakiTestHelper::getLangByCode('fi')->id;
        $properties['value'] = 'Suomi';
        self::createTypeRecord('ragnaroek_pageelement_lang', $properties); 
        // RU
        $properties['name'] = RakiTestHelper::LangRuName;
        $properties['lang'] = RakiTestHelper::getLangByCode('ru')->id;
        $properties['value'] = 'Русский';
        self::createTypeRecord('ragnaroek_pageelement_lang', $properties); 

    }

    public static function prepareContent()
    {
        self::createStorage();
        self::importLanguages();
        self::createContent();
    }
}

?>
