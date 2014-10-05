<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\Token;
use gossi\formatter\token\Tokenizer;

class NewlineFormatter extends AbstractSpecializedFormatter {
	
	protected function doVisit(Token $token) {
		$this->preOpenCurlyBrace($token);
		$this->postCloseCurlyBrace($token);
	}
	
	private function preOpenCurlyBrace(Token $token) {
		if ($token->contents == '{') {
			$structural = $this->context->getStructuralContext();
				
			// curly braces in strucs
			if (in_array($structural->type, Tokenizer::$STRUCTS)) {
				$this->newlineOrSpaceBeforeCurly($this->config->getBraces('struct') == 'next');
			}
				
			// curly braces in functions
			else if ($structural->type == T_FUNCTION) {
				$this->newlineOrSpaceBeforeCurly($this->config->getBraces('function') == 'next');
			}
				
			// curly braces in blocks
			if (in_array($structural->type, Tokenizer::$BLOCKS)) {
				$this->newlineOrSpaceBeforeCurly($this->config->getBraces('blocks') == 'next');
			}
			
			// new line after open curly brace
			$this->defaultFormatter->addPostWriteln();
		}
	}
	
	private function postCloseCurlyBrace(Token $token) {
		if ($token->contents == '}') {
			$structural = $this->context->getStructuralContext();
				
			// check new line before T_ELSE and T_ELSEIF
			if (in_array($structural->type, [T_IF, T_ELSEIF])
					&& in_array($this->nextToken->type, [T_ELSE, T_ELSEIF])) {
				$this->newlineOrSpaceAfterCurly($this->config->getNewline('elseif_else'));
			}

			// check new line before T_CATCH
			else if ($this->nextToken->type == T_CATCH) {
				$this->newlineOrSpaceAfterCurly($this->config->getNewline('catch'));
			}
				
			// check new line before finally
			else if ($token->contents == 'finally') {
				$this->newlineOrSpaceAfterCurly($this->config->getNewline('finally'));
			}
						
			// check new line before T_CATCH
			else if ($structural->type == T_DO
					&& $this->nextToken->type == T_WHILE) {
				$this->newlineOrSpaceAfterCurly($this->config->getNewline('do_while'));
			}

			// anyway a new line
			else {
				$this->defaultFormatter->addPostWriteln();
			}
		}
	}
	
	private function newlineOrSpaceBeforeCurly($condition) {
		if ($condition) {
			$this->writer->writeln();
		} else if ($this->config->getWhitespace('before_curly')) {
			$this->writer->write(' ');
		}
	}
	
	private function newlineOrSpaceAfterCurly($condition) {
		if ($condition) {
			$this->writer->writeln();
		} else {
			$this->defaultFormatter->addPostWrite(' ');
		}
	}
}