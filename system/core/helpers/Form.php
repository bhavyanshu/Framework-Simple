<?php
/**
 * Form Hellper Class
 */
/**
 * This handles the creation of forms and validation of forms
 * @category   Helpers
 * @package    Core
 * @subpackage Helpers
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */
	class Form {
		/**
		 * Create a form
		 * @api
		 * @param  string $action  the action of the form
		 * @param  string $method  the method of the form
		 * @param  array $inputs  the inputs you want created
		 * @param  string $enctype the encode type
		 * @return string          the html form
		 */
		public function create_form($action, $method, $inputs, $enctype=NULL) {
			$enctype = empty($enctype)?"":"enctype='{$enctype}'";
			$html = "<form action='{$action}' method='{$method}' ".$enctype.">\n\t<div>\n";
			foreach($inputs as $input) {
				$type = empty($input["type"])?"text":$input["type"];
				$name = empty($input["name"])?"":$input["name"];
				$value = empty($input["value"])?"":$input["value"];
				$id = empty($input["id"])?"":$input["id"];
				$label = empty($input["label"])?"":$input["label"];

				if (!empty($label)) {
					$labelHtml = "\t\t<label";
					$labelHtml .= empty($id)?">":" for='{$id}'>";
					$labelHtml .= "{$label}</label>\n";

					$html .= $labelHtml;
				};

				$inputHtml = "\t\t<input type='{$type}' ";
				$inputHtml .= empty($name)?"":"name='{$name}'";
				$inputHtml .= empty($value)?"":"value='{$value}'";
				$inputHtml .= empty($id)?"":"id='{$id}'";
				$inputHtml .= "/>";

				$html .= "{$inputHtml}\n";

			}
			$html.= "\t</div>\n</form>";
			echo $html;
		}

		/**
		 * validate a form
		 * @api
		 * @param  string $method     the method
		 * @param  string $submitName the name of the submit button
		 * @param  array $inputs     the inputs to validate
		 * @return array             the fields or the error strings
		 */
		public function validate($method,$submitName,$inputs) {
			$scope = $method=="get"?$_GET:$_POST;
			$valid = true;
			$errorStrings = array("valid"=>false);
			$fields = array("valid"=>true);
			if(!empty($scope["{$submitName}"])) {
				foreach($inputs as $input) {
					$name = $input["name"];
					$label = empty($input["label"])?$input["name"]:$input["label"];
					$default = empty($input["default"])?"":$input["default"];
					$inputString = $this -> param($scope, $name,$default);
					$limit = empty($input["limit"])?"":$input["limit"];
					$required = empty($input["required"])?true:false;
					if(!empty($inputString)) {
						if (!$this -> validateType($input["type"], $inputString, $limit)) {
							array_push($errorStrings, array("name"=>$name, "errorString"=>"{$label} is incorrect"));
							$valid = false;
						}else {
							$fields[":{$name}"] = $inputString;
						}
					}elseif($required) {
						array_push($errorStrings, array("name"=>$name, "errorString"=>"{$label} is required"));
						$valid = false;
					}
				}
				return $valid?$fields:$errorStrings;
			}

		}
		/**
		 * Valiate based on type
		 * @param  string $type   the type of validation
		 * @param  string $string the string to validate
		 * @param  string $limit  extra information
		 * @return boolean        if valid
		 */
		private function validateType($type, $string, $limit=NULL) {
			switch($type) {
				case "email":
					$valid = preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $string);
					break;
				case "phone":
					$valid = preg_match("/^(((\(\d{3}\)|\d{3})( |-|\.))|(\(\d{3}\)|\d{3}))?\d{3}( |-|\.)?\d{4}(( |-|\.)?([Ee]xt|[Xx])[.]?( |-|\.)?\d{4})?$/", $string);
					break;
				case "integer":
					$valid = preg_match("/^[-+]?\d*$/", $string);
					break;
				case "url":
					$valid = preg_match("/^http\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?$/", $string);
					break;
				case "date":
					$parts="";
					$valid = preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/", $string, $parts);
					$valid = $valid?checkdate($parts[1],$parts[2],$parts[3]):NULL;
					break;
				case "regex":
					$valid = preg_match($limit, $string);
					break;
				case "length":
					$valid = strlen($string) > $limit;
					break;
			}
			return $valid;
		}

		/**
		 * return params
		 * @param  array $scope   the scope of the param
		 * @param  string $name    the name of the param
		 * @param  string $default the default
		 * @return string          param
		 */
		private function param($scope, $name, $default="") {
			if (empty($scope[$name])) {
				return $default;
			}else {
				return $scope[$name];
			}
		}

}