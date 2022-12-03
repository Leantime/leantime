<?php

namespace leantime\domain\services {

    use GuzzleHttp\Client;
    use GuzzleHttp\Promise\PromiseInterface;
    use leantime\core;
    use leantime\domain\repositories;
    use Ramsey\Uuid\Uuid;

    class plugins
    {

        private $pluginRepository;
        private $pluginDirectory =  ROOT."/../src/plugins/";


        public function __construct()
        {
            $this->pluginRepository = new repositories\plugins();

        }

        public function getAllPlugins() {
            return $this->pluginRepository->getAllPlugins(false);
        }

        public function isPluginEnabled($pluginFolder) {

            $plugins = $this->getEnabledPlugins();

            foreach($plugins as $plugin) {
                if(strtolower($plugin->foldername) == strtolower($pluginFolder)){
                    return true;
                }
            }

            return false;
        }

        public function getEnabledPlugins() {

            if(!isset($_SESSION['enabledPlugins'])){
                $_SESSION['enabledPlugins'] = $this->pluginRepository->getAllPlugins(true);
            }

            return $_SESSION['enabledPlugins'];
        }

        public function discoverNewPlugins() {

            $installedPlugins = $this->getAllPlugins();
            //Simplify list of installed plugin for a quicker array_Search
            $installedPluginNames = array();

            foreach($installedPlugins as $plugin) {
                $installedPluginNames[] = $plugin->foldername;
            }

            $scanned_directory = array_diff(scandir($this->pluginDirectory), array('..', '.'));

            $newPlugins = [];

            foreach($scanned_directory as $directory) {


                if(is_dir($this->pluginDirectory."/".$directory) && array_search($directory, $installedPluginNames) === false){

                    $pluginJsonFile = $this->pluginDirectory."/".$directory."/plugin.json";

                    if(is_file($pluginJsonFile)) {

                        $json = file_get_contents($pluginJsonFile);

                        $pluginFile = json_decode($json,true);
                        $plugin = new \leantime\domain\models\plugins();
                        $plugin->name = $pluginFile['name'];
                        $plugin->enabled = 0;
                        $plugin->description = $pluginFile['description'];
                        $plugin->version= $pluginFile['version'];
                        $plugin->installdate = '';
                        $plugin->foldername = $directory;
                        $plugin->homepage = $pluginFile['homepage'];
                        $plugin->authors = $pluginFile['authors'];

                        $newPlugins[] = $plugin;
                    }
                }
            }

            return $newPlugins;

        }

        public function installPlugin($pluginFolder) {

            $pluginFolder = strip_tags(stripslashes($pluginFolder));
            $pluginJsonFile = $this->pluginDirectory."/".$pluginFolder."/plugin.json";

            if(is_file($pluginJsonFile)) {

                $json = file_get_contents($pluginJsonFile);

                $pluginFile = json_decode($json,true);
                $plugin = new \leantime\domain\models\plugins();
                $plugin->name = $pluginFile['name'];
                $plugin->enabled = 0;
                $plugin->description = $pluginFile['description'];
                $plugin->version= $pluginFile['version'];
                $plugin->installdate = date("Y-m-d");
                $plugin->foldername = $pluginFolder;
                $plugin->homepage = $pluginFile['homepage'];
                $plugin->authors = json_encode($pluginFile['authors']);

                return $this->pluginRepository->addPlugin($plugin);
            }

            return false;

        }

        public function enablePlugin(int $id) {
            unset($_SESSION['enabledPlugins']);
            return $this->pluginRepository->enablePlugin($id);
        }

        public function disablePlugin(int $id) {
            unset($_SESSION['enabledPlugins']);
            return $this->pluginRepository->disablePlugin($id);

        }

        public function removePlugin(int $id) {
            unset($_SESSION['enabledPlugins']);
            return $this->pluginRepository->removePlugin($id);

            //TODO remove files savely

        }



    }

}
