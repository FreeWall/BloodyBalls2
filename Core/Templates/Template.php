<?php
namespace Core\Templates;

use Core\Application;
use Core\Environment;
use Core\Utils\Validators;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class Template extends BaseTemplate {

	public function __construct($file,array $params = []){
		parent::__construct($file,$params);
		$this->addParams(["global" => Application::self()->getGlobalParams()]);
		$set = new MacroSet($this->getLatte()->getCompiler());
		$set->addMacro("link",[$this,'macroLink']);
		$set->addMacro("helplink",[$this,'macroHelpLink']);
		$set->addMacro("control",[$this,'macroControl']);
		$set->addMacro("form",[$this,'macroFormBegin'],[$this,'macroFormEnd']);
		$set->addMacro("formContainer",[$this,'macroFormContainer'],[$this,'macroFormContainerEnd']);
		$set->addMacro("formStatus",[$this,'macroFormStatus']);
		$set->addMacro("label",[$this,'macroLabel'],[$this,'macroLabelEnd'],null,MacroSet::AUTO_EMPTY);
		$set->addMacro("input",[$this,'macroInput']);
		$set->addMacro("paginator",[$this,'macroPaginator']);
	}

	public function macroLink(MacroNode $node,PhpWriter $writer){
		$name = $node->tokenizer->fetchWords();
		if(count($name) == 1) $name = trim($name[0]," \"");
		else $name = strtoupper(trim($name[0]," \"")).":".trim($name[1]," \"");
		$name = $writer->formatWord($name);
		$tokens = $node->tokenizer;
		$pos = $tokens->position;
		$param = $writer->formatArray();
		$tokens->position = $pos;
		while($tokens->nextToken()){
			if($tokens->isCurrent('=>') && !$tokens->depth){
				$wrap = true;
				break;
			}
		}
		if(empty($wrap)){
			$param = substr($param,1,-1);
		}
		return $writer->write('echo \Core\Router\Router::get('.$name.')->generate('.$param.');');
	}

	public function macroHelpLink(MacroNode $node,PhpWriter $writer){
		$id = $node->tokenizer->fetchWord();
		if($id == null || !Validators::isNumericInt($id)){
			throw new \Exception('Invalid id in macro '.$node->getNotation());
		}
		$id = $writer->formatWord($id);
		return $writer->write('$article = new \Models\Help\HelpArticle('.$id.');'
		. 'echo \Core\Router\Router::get("HELP:article")->generate(["category" => \Models\Help\HelpCategory::TYPES[$article->getCategory()]["url"],"name" => $article->getUrl()]);');
	}

	public function macroControl(MacroNode $node,PhpWriter $writer){
		$name = $node->tokenizer->fetchWords();
		$method = "run";
		if(count($name) == 1) $name = trim($name[0]," \"");
		else {
			$name = trim($name[0]," \"");
			$method = trim($name[1]," \"");
		}
		$name = $writer->formatWord($name);
		$tokens = $node->tokenizer;
		$pos = $tokens->position;
		$param = $writer->formatArray();
		$tokens->position = $pos;
		while($tokens->nextToken()){
			if($tokens->isCurrent('=>') && !$tokens->depth){
				$wrap = true;
				break;
			}
		}
		if(empty($wrap)){
			$param = substr($param,1,-1);
		}
		if(!empty($param)) return $writer->write('\Core\Application::getComponentClass('.$name.','.$param.')->'.$method.'();');
		return $writer->write('\Core\Application::getComponentClass('.$name.')->'.$method.'();');
	}

	public function macroFormBegin(MacroNode $node,PhpWriter $writer){
		$name = $node->tokenizer->fetchWord();
		if($name == null){
			throw new \Exception('Missing form name in '.$node->getNotation());
		}
		$node->replaced = true;
		$node->tokenizer->reset();
		return $writer->write(
			"/* line $node->startLine */\n"
			. 'echo \Forms\Rendering\RuntimeFormRenderer::renderFormBegin($_form = $this->global->formsStack[] = '
			. ($name[0] === '$' ? 'is_object(%node.word) ? %node.word : ' : '')
			. '$this->global->uiControl[%node.word], %node.array);'
		);
	}

	public function macroFormEnd(MacroNode $node,PhpWriter $writer){
		return $writer->write('echo \Forms\Rendering\RuntimeFormRenderer::renderFormEnd(array_pop($this->global->formsStack));');
	}

	public function macroFormContainer(MacroNode $node,PhpWriter $writer){
		if($node->modifiers){
			throw new \Exception('Modifiers are not allowed in '.$node->getNotation());
		}
		$name = $node->tokenizer->fetchWord();
		if($name == null){ // null or false
			throw new \Exception('Missing name in '.$node->getNotation());
		}
		$node->tokenizer->reset();
		return $writer->write(
			'$this->global->formsStack[] = $formContainer = $_form = '
			.($name[0] === '$' ? 'is_object(%node.word) ? %node.word : ' : '')
			.'end($this->global->formsStack)[%node.word];'
		);
	}

	public function macroFormContainerEnd(MacroNode $node,PhpWriter $writer){
		return $writer->write('array_pop($this->global->formsStack); $formContainer = $_form = end($this->global->formsStack)');
	}

	public function macroFormStatus(MacroNode $node,PhpWriter $writer){
		$name = $node->tokenizer->fetchWord();
		if($name == null){
			throw new \Exception('Missing form name in '.$node->getNotation());
		}
		$node->replaced = true;
		$node->tokenizer->reset();
		return $writer->write(
			"/* line $node->startLine */\n"
			. 'echo \Forms\Rendering\RuntimeFormRenderer::renderFormStatus('.($name[0] === '$' ? 'is_object(%node.word) ? %node.word : ' : '')
			. '%node.word);'
		);
	}

	public function macroLabel(MacroNode $node,PhpWriter $writer){
		if($node->modifiers){
			throw new \Exception('Modifiers are not allowed in '.$node->getNotation());
		}
		$words = $node->tokenizer->fetchWords();
		if(!$words){
			throw new \Exception('Missing name in '.$node->getNotation());
		}
		$node->replaced = true;
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$' ? '$_input = is_object(%0.word) ? %0.word : end($this->global->formsStack)[%0.word]; if ($_label = $_input' : 'if ($_label = end($this->global->formsStack)[%0.word]')
			.'->%1.raw) echo $_label'
			.($node->tokenizer->isNext() ? '->addAttributes(%node.array)' : ''),
			$name,
			$words ? ('getLabelPart('.implode(', ',array_map([$writer,'formatWord'],$words)).')') : 'getLabel()'
		);
	}

	public function macroLabelEnd(MacroNode $node,PhpWriter $writer){
		if($node->content != null){
			$node->openingCode = rtrim($node->openingCode,'?> ').'->startTag() ?>';
			return $writer->write('if ($_label) echo $_label->endTag()');
		}
	}

	public function macroInput(MacroNode $node,PhpWriter $writer){
		if($node->modifiers){
			throw new \Exception('Modifiers are not allowed in '.$node->getNotation());
		}
		$words = $node->tokenizer->fetchWords();
		if(!$words){
			throw new \Exception('Missing name in '.$node->getNotation());
		}
		$node->replaced = true;
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$' ? '$_input = is_object(%0.word) ? %0.word : end($this->global->formsStack)[%0.word]; echo $_input' : 'echo end($this->global->formsStack)[%0.word]')
			.'->%1.raw'
			.($node->tokenizer->isNext() ? '->addAttributes(%node.array)' : '')
			." /* line $node->startLine */",
			$name,
			$words ? 'getControlPart('.implode(', ',array_map([$writer,'formatWord'],$words)).')' : 'getControl()'
		);
	}

	public function macroPaginator(MacroNode $node,PhpWriter $writer){
		$name = $node->tokenizer->fetchWord();
		if($name == null){
			throw new \Exception('Missing paginator object in '.$node->getNotation());
		}
		$node->replaced = true;
		$node->tokenizer->reset();
		return $writer->write(
			"/* line $node->startLine */\n"
			."?><div class='foot'>\n"
			."	<div class='results'><? echo %node.word['counts'] ?>\n"
			."	</div><div class='pagination'><? echo %node.word['pages'] ?></div>\n"
			."</div><?"
		);
	}
}