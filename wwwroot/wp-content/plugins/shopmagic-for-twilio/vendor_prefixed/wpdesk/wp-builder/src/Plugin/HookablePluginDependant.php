<?php

namespace ShopMagicTwilioVendor\WPDesk\PluginBuilder\Plugin;

interface HookablePluginDependant extends \ShopMagicTwilioVendor\WPDesk\PluginBuilder\Plugin\Hookable
{
    /**
     * Set Plugin.
     *
     * @param AbstractPlugin $plugin Plugin.
     *
     * @return null
     */
    public function set_plugin(\ShopMagicTwilioVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin $plugin);
    /**
     * Get plugin.
     *
     * @return AbstractPlugin.
     */
    public function get_plugin();
}
