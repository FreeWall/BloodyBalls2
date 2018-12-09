<?php
namespace Core\Utils\Table\Filterer;

interface ITableFilterParam {

	public function getValue();

	public function setValue($value);

	public function isValid($data):bool;

	public function getQuery():?string;
}