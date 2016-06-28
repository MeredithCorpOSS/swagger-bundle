<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array();
        $bundles[] = new TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\TestBundle();

        if ($this->getEnvironment() == 'test_annotation_override') {
            $bundles[] = new TimeInc\SwaggerBundle\Tests\fixtures\TestApp\OverrideTestBundle\OverrideTestBundle();
        }

        if (in_array($this->getEnvironment(), array('test', 'test_annotation_override'))) {
            $bundles[] = new Symfony\Bundle\FrameworkBundle\FrameworkBundle();
            $bundles[] = new TimeInc\SwaggerBundle\SwaggerBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/../var/cache/'.$this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return $this->rootDir.'/../var/logs';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if (file_exists(__DIR__.'/config/config_'.$this->getEnvironment().'.yml')) {
            $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
        } else {
            $loader->load(__DIR__.'/config/config.yml');
        }
    }
}
