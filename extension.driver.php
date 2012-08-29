<?php

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
            $options = 'apikey=' . Symphony::Configuration()->get('apikey', 'bitly') . '&';
            $options .= 'login=' . Symphony::Configuration()->get('login', 'bitly') . '&';
            $options .= 'longUrl=' . $context["params"]["current-url"];

            $result = file_get_contents('https://api-ssl.bitly.com/v3/shorten/?' . $options);
            $data = json_decode($result, true);
            $context["params"]["tinyurl"] = $data["data"]["url"];
		}

    }

?>