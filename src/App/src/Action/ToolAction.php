<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;

class ToolAction implements ServerMiddlewareInterface
{
	private $router;
	
	private $template;
	
	public function __construct(Router\RouterInterface $router, Template\TemplateRendererInterface $template)
	{
		$this->router   = $router;
		$this->template = $template;
	}
	
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
    	$data = [
    			'layout' => 'layout::swagger',
    	];
    	return new HtmlResponse($this->template->render('app::swagger', $data));
    }
}
