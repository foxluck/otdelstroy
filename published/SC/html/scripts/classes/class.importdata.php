<?php
class ImportData
{
	protected $csv_pointer = null;
	protected $csv_size = null;
	protected $target_cols = array();
	protected $primary_cols = array();
	protected $import_handler = null;
	public $primary_col = null;
	protected $source_cols = null;
	protected $data_mapping = array();
	public $delimeter = ';';
	public $lines = array();
	public $configurator_titles = array();
	protected $source_column_count = 0;

	function __construct($csv_file_name,$delimeter = ';')
	{
		if(setlocale(LC_CTYPE, 'ru_RU.UTF-8', 'en_US.UTF-8') === false){
			trigger_error("Error while setlocale",E_USER_ERROR);
		}
		if(file_exists($csv_file_name)){
			$this->csv_size = filesize( $csv_file_name );
			$this->csv_pointer =fopen($csv_file_name, "r" );
		}
		$this->delimeter = $delimeter;
	}

	function __destruct()
	{
		if($this->csv_pointer){
			fclose($this->csv_pointer);
		}
	}

	function import()
	{
		$args = func_get_args();
		while($line = $this->readCsvLine()){
			$data = $this->applyDataMapping($line);
			if($this->import_handler){
				$result = call_user_func($this->import_handler,$data,$this->primary_col,$args);
			}
		}
		return $result;
	}

	function setImportHandler($function)
	{
		if(function_exists($function)){
			$this->import_handler = $function;
		}else{
			$this->import_handler = create_function('$data',$function);
		}
	}


	function readCsv()
	{
		if(!$this->csv_pointer){
			return false;
		}
		while( $line = $this->readCsvLine()){
			$this->lines = $line;
		}
		return $this->lines;
	}

	function readCsvLine()
	{
		static $columnCount = 0;
		static $counter = 0;

		if(!$this->csv_pointer||!$this->csv_size){
			return false;
		}
		do{//skip empty lines
			$line = fgetcsv($this->csv_pointer, $this->csv_size, $this->delimeter);
			$count_empty_cells = 0;
			$is_empty_line = true;
			if($line){
				foreach($line as $cell){
					if($cell){
						$is_empty_line =false;
						break;
					}
				}
			}
		}while($line&&$is_empty_line);

		if($line === false){
			return false;
		}
		foreach($line as &$cell){
			$cell = utf8_bad_replace($cell);
		}
		$currentColumnCount = count($line);
		if($columnCount>$currentColumnCount){
			$line = array_merge($line,array_fill(0,$columnCount-$currentColumnCount,''));
		}else{
			$columnCount = count($line);
		}
		if(!$this->source_cols&&$line){
			$this->setSourceColumns($line);
		}

		$this->source_column_count = $columnCount;
		return $line;
	}

	function setSourceColumns($source_cols){
		$names = array();
		foreach($source_cols as $col=>$name){
			if(isset($names[$name])){
				unset($source_cols[$col]);
				$key = array_search($name,$this->source_cols);
				unset($this->source_cols[$key]);
				$this->source_cols[$key.':'.$col]=$name;
			}else{
				$names[$name] = true;
				$this->source_cols[$col]=$name;
			}
		}
	}

	function setTargetColumns($target_cols)
	{
		$this->target_cols = $target_cols;
	}

	function setPrimaryCols($primary_cols)
	{
		$this->primary_cols = $primary_cols;
	}

	function setConfiguratorHeader($titles)
	{
		$this->configurator_titles = $titles;
	}

	function getDataMappingHtmlConfigurator($show_header = true,$show_groups = false,$default_primary = null)
	{
		$html_configurator = '';
		if($default_primary){
			$default_primary =  LanguagesManager::ml_getLangFieldName($default_primary);
		}
		$checked = true;
		$col_count = 0;
		if($show_header&&(count($this->configurator_titles)==4)){
			$html_configurator .= '<tr class="gridsheader" width="100%" cellspacing="0" cellpadding="0" border="0">';
			foreach ($this->configurator_titles as $title){
				$html_configurator .= '<td><i>'.translate($title).'</i></td>';
			}
			$html_configurator .= '</tr>';
		}
		foreach($this->target_cols as $group=>$group_cols){
			if(!$group_cols||!count($group_cols)){
				continue;
			}
			if($show_groups){
				$html_configurator .= '<tr><td colspan="3">'.translate($group).'</td></tr>';
			}
			foreach($group_cols as $col_id=>$col_description){
				$html_configurator .= '<tr class="gridline'.($col_count%2?'':'1').'">';

				$html_configurator .= '<td>';
				$html_configurator .= $this->getHtmlSourceSelector("data_mapping_source_col_{$col_count}",$col_description);
				$html_configurator .= '</td>';
				$html_configurator .= '<td>&rarr;</td><td>';
				$html_configurator .= '<input type="hidden" name="data_mapping_target_field_'.$col_count.'" value="'.$col_id.'">';
				$html_configurator .= '<input type="hidden" name="data_mapping_target_group_'.$col_count.'" value="'.$group.'">';




				if(isset($this->primary_cols[$col_id])){
					$html_configurator .= '<label for="data_mapping_primary_'.$col_id.'"><i>'.$col_description.'</i></label>';
				}else{
					$html_configurator .= $col_description;
				}
				$html_configurator .= '</td>';
				$html_configurator .= '<td  class="endgrid">';
				if(isset($this->primary_cols[$col_id])){
					$checked = $default_primary&&($col_id == $default_primary)?true:$checked;
					$html_configurator .= '<input type="radio" id="data_mapping_primary_'.$col_id.'" name="data_mapping_primary_col" value="'.$col_id.'"'.($checked?' checked':'').'>';
					$checked = false;
				}else{
					$html_configurator .= "&nbsp;";
				}
				$html_configurator .= '</td>';

				$html_configurator .= '</tr>';
				$col_count++;

			}
		}
		return $this->validateSourceFields().'<table class="grid">'.$html_configurator.'</table>';

	}

	function applyDataMapping($line)
	{
		$data = array();
		foreach($this->data_mapping as $target_group=>$group_fields){
			if(!isset($data[$target_group])){
				$data[$target_group] = array();
			}
			foreach($group_fields as $field=>$col){
				if(is_array($col)){
					$data[$target_group][$field] = array();

					foreach($col as $col_item){
						if(isset($line[$col_item])&&$line[$col_item]){
							$data[$target_group][$field][] = $line[$col_item];
						}
					}
				}elseif($col>=0){
					$data[$target_group][$field] = isset($line[$col])?$line[$col]:null;
				}
			}
		}
		return $data;
	}

	function parseDataMapping()
	{
		$data_mapping = scanArrayKeysForID($_POST,array('data_mapping_source_col','data_mapping_target_group','data_mapping_target_field'));
		foreach($data_mapping as $data_mapping_item){
			$column = $data_mapping_item['data_mapping_source_col'];
			$group = $data_mapping_item['data_mapping_target_group'];
			$field = $data_mapping_item['data_mapping_target_field'];
			if(!isset($this->data_mapping[$group])){
				$this->data_mapping[$group] = array();
			}
			$columns = array_map('intval',explode(':',$column));
			$this->data_mapping[$group][$field ] = count($columns)>1?$columns:(count($columns)?$columns[0]:-1);
		}
		$this->primary_col = isset($_POST['data_mapping_primary_col'])?$_POST['data_mapping_primary_col']:null;
	}

	function validateSourceFields()
	{
		$counts = array();
		foreach($this->source_cols as $col_id=>$col_name){
			if(isset($counts[$col_name])){
				$counts[$col_name]++;
			}else{
				$counts[$col_name] = 1;
			}
		}
		$res = '';
		foreach($counts as $col_name=>$count){
			if($count>1){
				$res .= sprintf("%s meets %d times<br>\n",$col_name,$count);
			}
		}
		return $res?'Warning: '.$res:$res;
	}

	function getSourceColumns()
	{
		return $this->source_cols;
	}

	function getSourceColumnCount()
	{
		return $this->source_column_count;
	}

	protected function getHtmlSourceSelector($name,$selected_name_like)
	{

		$selected_id = -1;
		$max_similar_percent = 0;

		if(is_null($this->source_cols)){
			$this->readCsvLine();
		}

		if(is_null($this->source_cols)){
			return translate('error_not_find_col_names');
		}

		foreach($this->source_cols as $source_col_id=>$source_col_name){
			$percent = 0;
			similar_text( $source_col_name, $selected_name_like,$percent);
			if($percent>$max_similar_percent){
				$max_similar_percent = $percent;
				$selected_id = $source_col_id;
			}
			if($max_similar_percent == 100)break;
		}
		if($max_similar_percent<90){
			$selected_id = -1;
		}
		$source_select = '<option value="-1"'.($selected_field==-1?' selected="selected"':'').' style="color:grey">'.translate('prdimport_csv_ignorecolumn').'</option>';

		foreach($this->source_cols as $source_col_id=>$source_col_name){
			$source_col_ids = array_map('intval',explode(':',$source_col_id));
			$selected = (in_array($selected_id,$source_col_ids))?' selected="selected" style="font-style: italic;text-decoration:underline"':'';
			$source_select .= '<option value="'.$source_col_id.'"'.$selected.'>';
			$source_select .= $source_col_id.': '.htmlspecialchars($source_col_name);
			$source_select .= '</option>';
		}

		return '<select name="'.$name.'">'.$source_select.'</select>';

	}

}
?>