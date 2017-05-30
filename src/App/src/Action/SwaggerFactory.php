<?php
namespace App\Action;

use Interop\Container\ContainerInterface;
use Swagger\StaticAnalyser as SwaggerStaticAnalyser;
use Swagger\Analysis as SwaggerAnalysis;
use Swagger\Util as SwaggerUtil;
use App\Option\ModuleOptions;

class SwaggerFactory
{
	public function __invoke(ContainerInterface $container)
	{
		$config = $container->get('config');
		$config = (isset($config['swagger']) ? $config['swagger'] : null);
		if ($config === null) {
			throw new \RuntimeException('Configuration for SwaggerModule was not found');
		}
		
		$options = new ModuleOptions($config);
		$analyser = new SwaggerStaticAnalyser();
		$analysis = new SwaggerAnalysis();
		$processors = SwaggerAnalysis::processors();
		// Crawl directory and parse all files
		$paths = $options->getPaths();
		foreach ($paths as $directory) {
			$finder = SwaggerUtil::finder($directory);
			foreach ($finder as $file) {
				$analysis->addAnalysis($analyser->fromFile($file->getPathname()));
			}
		}
		// Post processing
		$analysis->process($processors);
		// Validation (Generate notices & warnings)
		$analysis->validate();
		// Pass options to analyzer
		$resourceOptions = $options->getResourceOptions();
		if (!empty($resourceOptions['defaultBasePath'])) {
			$analysis->swagger->basePath = $resourceOptions['defaultBasePath'];
		}
		if (!empty($resourceOptions['defaultHost'])) {
			$analysis->swagger->host = $resourceOptions['defaultHost'];
		}
		if (!empty($resourceOptions['schemes'])) {
			$analysis->swagger->schemes = $resourceOptions['schemes'];
		}
		
		return new SwaggerAction($analysis->swagger);
	}
}
