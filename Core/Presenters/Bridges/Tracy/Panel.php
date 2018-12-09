<?php
namespace Core\Presenters\Bridges\Tracy;

use Core\Presenters\Presenter;
use Tracy;
use Tracy\Dumper;

class Panel implements Tracy\IBarPanel {

	private $presenter;
	private $source;

	public function __construct(Presenter $presenter){
		$this->presenter = $presenter;
		$this->source = new \ReflectionClass(get_class($this->presenter));
		Tracy\Debugger::getBar()->addPanel($this);
	}

	public function getTab(){
		return '<span title="Router"><svg viewBox="0 0 2048 2048"><path fill="#d86b01" d="m1559.7 1024c0 17-6 32-19 45l-670 694.48c-13 13-28 19-45 19s-32-6-45-19-19-28-19-45v-306.48h-438.52c-17 0-32-6-45-19s-19-28-19-45v-642c0-17 6-32 19-45s28-19 45-19h438.52v-309.41c0-17 6-32 19-45s28-19 45-19 32 6 45 19l670 691.41c13 13 19 28 19 45z"/><path d="m1914.7 1505c0 79-31 147-87 204-56 56-124 85-203 85h-320c-9 0-16-3-22-9-14-23-21-90 3-110 5-4 12-6 21-6h320c44 0 82-16 113-47s47-69 47-113v-962c0-44-16-82-47-113s-69-47-113-47h-312c-11 0-21-3-30-9-15-25-21-90 3-110 5-4 12-6 21-6h320c79 0 147 28 204 85 56 56 82 124 82 204-9 272 9 649 0 954z" fill-opacity=".5" fill="#d86b01"/></svg><span class="tracy-label">'
		. $this->escapeString($this->presenter->getName()).'</span>'
		. '</span>';
	}

	public function getPanel(){
		$templateParams = $this->presenter->getTemplate()->getParams();
		if(isset($templateParams['global'])) unset($templateParams['global']);
		return '
		<style>
		#tracy-debug code {
			display: block;
			background: transparent;
		}
		#tracy-debug tracy-PresenterProfiler tr table {
			margin: 8px 0; max-height: 150px; overflow:auto
		}
		</style>
		<h1>'.$this->escapeString($this->presenter->getName()).'</h1>
		<div class="tracy-inner tracy-PresenterProfiler">
		<p><a href="'.$this->escapeString(Tracy\Helpers::editorUri($this->source->getFileName(),$this->source->getStartLine())).'">'.$this->source->getName().'</a></p>
		<table>
		<tr><td><code>'.$this->escapeString($this->presenter->getRequest()->getHttpRequest()->getMethod()).'</code></td><td><code>'.$this->escapeString($this->presenter->getRequest()->getHttpRequest()->getUrl()->getBaseUrl()).(!empty($this->presenter->getRequest()->getHttpRequest()->getPost()) ? Dumper::toHtml($this->presenter->getRequest()->getHttpRequest()->getPost()) : '').(!empty($this->presenter->getRequest()->getHttpRequest()->getQuery()) ? Dumper::toHtml($this->presenter->getRequest()->getHttpRequest()->getQuery()) : '').'</code></td></tr>
		<tr><td>Route</td><td><code><a href="'.$this->escapeString(Tracy\Helpers::editorUri($this->presenter->getRequest()->getRoute()->getSource()['file'],$this->presenter->getRequest()->getRoute()->getSource()['line'])).'">'.$this->escapeString($this->presenter->getRequest()->getRoute()->getName()).'</a></code>'.(!empty($this->presenter->getRequest()->getParams()) ? Dumper::toHtml($this->presenter->getRequest()->getParams()) : '').'</td></tr>
		<tr><td>Template</td><td><code><a href="'.$this->escapeString(Tracy\Helpers::editorUri($this->presenter->getTemplate()->getDirectory().$this->presenter->getTemplate()->getFile())).'">'.$this->escapeString($this->presenter->getTemplate()->getFile()).'</a></code>'.(!empty($templateParams) ? Dumper::toHtml($templateParams) : '').'</td></tr>
		</table>
		</div>
		';
	}

	private function escapeString($s):string {
		return htmlspecialchars($s,ENT_IGNORE,'UTF-8');
	}
}