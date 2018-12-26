<?php
namespace Apps\Game\Presenters;

class IndexPresenter extends BasePresenter {

	public function render(){

		//echo bin2hex(openssl_random_pseudo_bytes(16));

		/*$content = "";
		$increment = 180/100;
		for($i=0;$i<=100;$i++){
			$content .= "
	&[data-progress=\"$i\"]{ div.mask.full, div.mask div.fill {.transform(rotate(".(-$increment*$i)."deg))} div.mask div.fill.fix {.transform(rotate(".(-$increment*$i*2)."deg))}}";
		}
		echo "<pre>".$content;*/
	}
}