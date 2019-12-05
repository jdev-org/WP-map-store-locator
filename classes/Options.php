<?php

class Options
{
    public $options;
    public $group;
    public $oldOptions;
    
    public __construct() {
        $this->options = array();
        $this->oldOptions = array();
    }

    /**
     * register
     *  Set name to identify option value saved
     *  option name | value
     */
    public register($group) {
        if ( !$group and get_plugin_data( __FILE__ )['Name']) {
            return $this->group = get_plugin_data( __FILE__ )['Name'];
        }
        return $this->group = $group;
    }

    /**
     * set new options
     */
    public setOption( $group, $value ) {
        register_setting($group, )
        
    }

    public setOldOptions($options) {
        $this->oldOption = $options;
    }

    public updateOption($option) {
        // archive
        $this->saveOldOptions($this->options);
        // update
        update_option($this->group, $this->options);
    }

    public destroyOption($name) {
        $this->saveOldOptions($this->options);
        unregister_setting($this->group, $name);
        $this->
    }
}