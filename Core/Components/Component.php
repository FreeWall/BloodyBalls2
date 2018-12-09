<?php
namespace Core\Components;

use Core\Application;
use Core\Templates\ITemplate;

abstract class Component implements IComponent {

	private $params;

	/** @var ITemplate */
	private $template;

	public function __construct(array $params = []){
		$this->params = $params;
	}

	public function getTemplate():ITemplate {
		return $this->template;
	}

	public function setTemplate(ITemplate $template){
		$this->template = $template;
	}

	public function getParams(){
		return $this->params;
	}

	public function run(){
		$this->render();
		$this->template->setDirectory(BASEDIR."apps/".Application::getAppName()."/components/");
		$this->template->render();
	}

	public abstract function render();
}