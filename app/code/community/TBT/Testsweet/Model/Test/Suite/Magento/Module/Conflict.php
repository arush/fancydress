<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Module_Conflict extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Check modules for rewrite conflicts');
    }

    public function getDescription() {
        return $this->__('Check modules for rewrite conflicts');
    }

    protected function generateSummary() {

        $conflicts = $this->getRewriteConflicts();

        if (empty($conflicts)) {
            $this->addPass($this->__("No extension conflict found"));
        }

        foreach ($conflicts as $conflict) {
            $string = $this->__("Conflict found -- ") . print_r($conflict, true);

            if (count($conflict) == 2) {
                $child = $conflict[0];
                $parent = $conflict[1];
                if (strpos($conflict[0]['rewrite_class_name'], 'TBT') > 0) {
                    $parent = $conflict[0];
                    $child = $conflict[1];
                }
                $string .= $this->__(":: Possible solution ::") . "\n";
                $string .= $this->__("backup and open: ") . "{$child['rewrite_class_file']} \n";
                $string .= $this->__("change: ") . "class {$child['rewrite_class_name']} extends {$child['rewrite_class_parent_name']}\n";
                $string .= $this->__("to: ") . "class {$child['rewrite_class_name']} extends {$parent['rewrite_class_name']}\n";
                $string .= $this->__("backup and open: ") . "{$child['config_file']} \n";
                $string .= $this->__("remove rewrite on: ") . "{$child['rewrite_class_parent_name']} \n";
                $string .= $this->__("Test!") . "\n";
            }

            $this->addWarning($string);
        }
    }

    /**
     * create an array with all config.xml files found in local and community
     * @return string[] 
     */
    protected function findConfigFiles() {
        $config_files = array();

        $targets = array(
            Mage::getStoreConfig('system/filesystem/code') . '/local',
            Mage::getStoreConfig('system/filesystem/code') . '/community'
        );

        foreach ($targets as $target) {
            if (!is_dir($target))
                continue;

            if (version_compare(phpversion(), '5.2.11', '>='))
                $directory = new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
            else
                $directory = new RecursiveDirectoryIterator($target);
            $iterator = new RecursiveIteratorIterator($directory);
            $foundfile = new RegexIterator($iterator, '/.*[\\/]config\.xml$/', RecursiveRegexIterator::GET_MATCH);
            foreach ($foundfile as $fullpath => $file) {
                $config_files[] = $fullpath;
            }
        }
        return $config_files;
    }

    /**
     * return all rewrites for a config.xml file
     *
     * @param string $configFilePath
     * @return string[] 
     */
    protected function findRewritesInConfigFiles($configs) {
        $results = array();

        foreach ($configs as $config) {
            $xml = simplexml_load_file($config);
            
            if (empty($xml)) {
                throw new Exception("Can't load config file:  $config");
            }

            $rewrites = $xml->xpath('//global/models/*/rewrite/*');
            $rewrites = $rewrites + $xml->xpath('//global/blocks/*/rewrite/*');

            foreach ($rewrites as $rewrite) {

                $path = $rewrite->xpath('parent::*/parent::*/parent::*'); // xpath '../' fails?
                $rewrite_type = (string) $path[0]->getName();
                $path = $rewrite->xpath('parent::*/parent::*'); 
                $module = (string) $path[0]->getName();
                $rewrite_obj = (string) $rewrite->getName();
                $rewrite_class = (string) trim($rewrite);

                $class_name = $rewrite_class;
                $class_file = 'error';
                $class_parent_name = 'error';
                $class_parent_file = 'error';
                try {
                    //$init = new $class_name;
                    //$ro = new ReflectionObject($init);
                    // this attemped failes because the class file cant load because of cd
                    
                    $ro = new ReflectionClass($class_name);
                    $ro_parent = $ro->getParentClass();

                    // let the magento system load the class this way it can do its magic
                    //$init = new $class_name();
                    //$ro_parent = new ReflectionObject($init);
                    //$ro_parent->getParentClass();
                    //$class_name = $ro->getName();
                    $class_file = $ro->getFileName();
                    $class_parent_name = $ro_parent->getName();
                    $class_parent_file = $ro_parent->getFileName();
                } catch (Exception $ex) {
                    $class_parent_file = $ex->getMessage();
                }

                $key = "{$rewrite_type}/{$module}/{$rewrite_obj}";
                if (!isset($results[$key])) {
                    $results[$key] = array();
                }

                $results[$key][] = array(
                    'rewrite_type' => $rewrite_type,
                    'config_file' => $config,
                    'module' => $module,
                    'model' => $rewrite_obj,
                    'rewrite_class_name' => $class_name,
                    'rewrite_class_file' => $class_file,
                    'rewrite_class_parent_name' => $class_parent_name,
                    'rewrite_class_parent_file' => $class_parent_file,
                );
            }
        }

        return $results;
    }

    /**
     *
     * @return array( array( ... ) ) 
     */
    protected function getRewriteConflicts() {
        $conflicts = array();
        $rewrites = $this->findRewritesInConfigFiles($this->findConfigFiles());
        foreach ($rewrites as $rewrite_key => $model_rewrites) {
            // if there is more then one rewrite working on the same module/model
            if (count($model_rewrites) > 1) {
                $conflicts[] = $model_rewrites;
            }
        }
        return $conflicts;
    }

}

?>