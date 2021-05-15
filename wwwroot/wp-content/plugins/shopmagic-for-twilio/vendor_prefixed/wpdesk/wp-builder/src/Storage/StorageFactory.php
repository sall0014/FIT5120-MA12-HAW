<?php

namespace ShopMagicTwilioVendor\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \ShopMagicTwilioVendor\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}
