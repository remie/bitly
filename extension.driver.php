<?php

    define('BITLY_CACHE', MANIFEST . '/cache/bitly');

    class Extension_bitly extends Extension {
    
    	/*-------------------------------------------------------------------------
    		Delegate
    	-------------------------------------------------------------------------*/
    
    	public function getSubscribedDelegates() {
    		return array(
    			array(
    				'page' => '/system/preferences/',
    				'delegate' => 'AddCustomPreferenceFieldsets',
    				'callback' => 'appendPreferences'
    			),
				array(
                    'page' => '/frontend/',
                    'delegate' => 'FrontendParamsResolve',
                    'callback' => 'frontendParamsResolve'
                )
    		);
    	}
    
    	/*-------------------------------------------------------------------------
    		Delegated functions
    	-------------------------------------------------------------------------*/	
    
    	public function appendPreferences($context){
    		$group = new XMLElement('fieldset',null,array('class'=>'settings'));
    		$group->appendChild(new XMLElement('legend', 'Bit.ly Authentication'));
    
    		
    		$div = new XMLElement('div',null,array('class'=>'group'));
    		$label = Widget::Label();
                    $input = Widget::Input('settings[bitly][login]', Symphony::Configuration()->get('login', 'bitly'), 'text');
                    $label->setValue(__('Username') . $input->generate());
                    $div->appendChild($label);
    		
    		$label = Widget::Label();
                    $input = Widget::Input('settings[bitly][apikey]', Symphony::Configuration()->get('apikey', 'bitly'), 'password');
                    $label->setValue(__('API Key') . $input->generate());
                    $div->appendChild($label);
    		$group->appendChild($div);
    
    		// Append preferences
    		$context['wrapper']->appendChild($group);
    	}
    
		public function frontendParamsResolve($context) {
            $url = $context["params"]["current-url"];

            try {
                $result = $this->getFromCache($url);

                if($result == false) {
                    $options = 'apikey=' . Symphony::Configuration()->get('apikey', 'bitly') . '&';
                    $options .= 'login=' . Symphony::Configuration()->get('login', 'bitly') . '&';
                    $options .= 'longUrl=' . $url;
                    $response = file_get_contents('https://api-ssl.bitly.com/v3/shorten/?' . $options);
                    $data = json_decode($response, true);
                    $result = $data["data"]["url"];
                }

                $context["params"]["tinyurl"] = $result;
                $this->persist($url, $result);
            } catch(Exception $exp) {
                $context["params"]["tinyurl"] = '';
            }
		}

        /*-------------------------------------------------------------------------
            Private functions
        -------------------------------------------------------------------------*/ 

        private function getFromCache($url) {
            if(file_exists(BITLY_CACHE)) {
                $cache = file_get_contents(BITLY_CACHE);
                $cache = json_decode($cache, true);

                $token = md5($url);
                if(array_key_exists($token, $cache[0])) {
                    return $cache[0][$token]["tinyurl"];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        private function persist($url, $result) {
            $cache = array();
            $cache[] = array();

            if(file_exists(BITLY_CACHE)) {
                $cache = file_get_contents(BITLY_CACHE);
                $cache = json_decode($cache, true);
            }

            $token = md5($url);
            if(!array_key_exists($token, $cache)) {
                $cache[0][$token] = array(
                    "url" => $url,
                    "tinyurl" => $result
                );
            }

            $cache = json_encode($cache);
            General::writeFile(BITLY_CACHE, $cache);
        }

    }

?>