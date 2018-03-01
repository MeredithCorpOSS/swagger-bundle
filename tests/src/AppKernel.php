<?php

namespace TimeInc\SwaggerBundle\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use TimeInc\SwaggerBundle\SwaggerBundle;
use TimeInc\SwaggerBundle\Tests\fixtures\TestApp\OverrideTestBundle\OverrideTestBundle;
use TimeInc\SwaggerBundle\Tests\fixtures\TestApp\TestBundle\TestBundle;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array();
        $bundles[] = new TestBundle();

        if ($this->getEnvironment() == 'test_annotation_override') {
            $bundles[] = new OverrideTestBundle();
        }

        if (in_array($this->getEnvironment(), array('test', 'test_annotation_override'))) {
            $bundles[] = new FrameworkBundle();
            $bundles[] = new SwaggerBundle();
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
        if (file_exists($this->rootDir.'/../config/config_'.$this->getEnvironment().'.yml')) {
            $loader->load($this->rootDir.'/../config/config_'.$this->getEnvironment().'.yml');
        } else {
            $loader->load($this->rootDir.'/../config/config.yml');
        }
    }
}
