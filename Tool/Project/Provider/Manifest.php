<?php
/**
 * Manifest
 *
 * @author Juan Minaya
 */

require_once 'Zrad/Tool/Project/Provider/Zrad.php';

class Zrad_Tool_Project_Provider_Manifest 
    implements Zend_Tool_Framework_Manifest_ProviderManifestable
{

    public function getProviders()
    {
        return array(
            'Zrad_Tool_Project_Provider_Zrad'
        );
    }
}