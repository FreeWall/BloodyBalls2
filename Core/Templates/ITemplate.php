<?php
namespace Core\Templates;

interface ITemplate {

	public function getFile():string;

	public function getDirectory():string;

	public function setDirectory(string $directory);

	public function getParams():array;

	public function addParams(array $params);

	public function addFilter(string $name,callable $callback);

	public function render();

	public function renderToString():string;
}