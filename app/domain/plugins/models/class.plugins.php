<?php

namespace leantime\domain\models {

    class plugins
    {
        public $id;
        public $name;
        public $enabled;
        public $description;
        public $version;
        public $installdate;
        public $foldername;
        public $homepage;
        public $authors;

        public function getPluginImageData() {
            $image = APP_ROOT.'/plugins/'.str_replace(".", '', $this->foldername).'/assets/screenshot.png';

            if(file_exists($image)) {
                // Read image path, convert to base64 encoding
                $imageData = base64_encode(file_get_contents($image));
                return 'data: '.mime_content_type($image).';base64,'.$imageData;
            }else{
                $image = APP_ROOT."/public/dist/images/svg/undraw_search_app_oso2.svg";
                $imageData = base64_encode(file_get_contents($image));
                return 'data: '.mime_content_type($image).';base64,'.$imageData;
            }

        }
    }
}
