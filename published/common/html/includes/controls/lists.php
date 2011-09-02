<?php

	class GenericListItem 
	{
		var $obj = null;
		var $objIdentifier = null;
		var $clickEventHandler = null;
		var $dblClickEventHandler = null;
	}

	//
	// Generic HTML list class
	//
	class GenericHtmlList
	{
		var $items = array();
		var $controlName = array();
		var $current = null;

		function render( $params, &$smarty_obj )
		//
		// Renders list in HTML format
		//
		{

		}

		function addItem( &$item )
		//
		// Adds item to the item list
		//
		//		Parameters:
		//			$item - object to add
		//
		//		Returns new item index
		//
		{
			$index = count($this->items);
			$this->items[$index] = $item;

			return $index;
		}

		function init()
		//
		// Initializes control
		//
		{
		}
	}

	//
	// Customizable list with selectable items
	//
	class CustomSelectableList extends GenericHtmlList
	{
		var $rowTemplate = null;
		var $unselectedColor = null;
		var $selectedColor = null;
		var $outputEmptyMessage = false;

		function CustomSelectableList( $name, $selectedColor, $unselectedColor )
		{
			$this->controlName = $name;
			$this->selectedColor = $selectedColor;
			$this->unselectedColor = $unselectedColor;
		}

		function init()
		{
			echo "<script language=JavaScript>
					var current_selected_element = null;
					var {$this->controlName} = new CustomSelectableList();
					{$this->controlName}.unselectedColor = \"{$this->unselectedColor}\";
					{$this->controlName}.selectedColor = \"{$this->selectedColor}\";
					{$this->controlName}.instanceName = \"{$this->controlName}\";
					{$this->controlName}.current = \"{$this->current}\";
			</script>";
		}

		function getElementId( $index )
		{
			return ($this->controlName)."item".$index;
		}

		function render($params, &$smarty_obj)
		{
			echo "<table border=0 cellpadding=0 cellspacing=0 width='100%'>";
			
			$count = count($this->items);
			
			($params['single_js']==1) ? $single_js = true : $single_js = false ;
			foreach ( $this->items as $index=>$item ) {
				
				$params = array();

				$first = $index == 0;
				$last = $index == $count-1;

				$params['smarty_include_tpl_file'] = $this->rowTemplate;
				$params['smarty_include_vars'] = array('item'=>$item->obj, 'first'=>$first, 'last'=>$last);

				$id = $this->getElementId($index);
				($single_js) ? print("<tr><td ID=\"$id\" style=\"cursor: pointer\" ondblclick=\"{$item->dblClickEventHandler}\" onClick=\"if(current_selected_element) current_selected_element.style.backgroundColor = '{$this->unselectedColor}'; this.style.backgroundColor = '{$this->selectedColor}';current_selected_element = this;{$item->clickEventHandler}\">") : print("<tr><td ID=\"$id\" style=\"cursor: pointer\" onselectstart=\"return false\">");

				$this->_addJsControlItem( $id, $index, $item );
				$smarty_obj->_smarty_include( $params );
				echo "</td></tr>";
			}

			if ( !count($this->items) && $this->outputEmptyMessage ) {
				$item = new GenericListItem();
				$id = -1;

				$params['smarty_include_tpl_file'] = $this->rowTemplate;
				$params['smarty_include_vars'] = array('item'=>$item->obj, 'first'=>$first, 'last'=>$last);

				($single_js) ? print("<tr><td ID=\"$id\" style=\"cursor: pointer\" ondblclick=\"{$item->dblClickEventHandler}\" onClick=\"if(current_selected_element) current_selected_element.style.backgroundColor = '{$this->unselectedColor}'; this.style.backgroundColor = '{$this->selectedColor}';current_selected_element = this;{$item->clickEventHandler}\" >") : print("<tr><td ID=\"$id\" style=\"cursor: pointer\" onselectstart=\"return false\">");

				$this->_addJsControlItem( $id, 0, $item );
				$smarty_obj->_smarty_include( $params );
				echo "</td></tr>";
			}

			echo "</table>";
			if ($single_js) 
				echo "<div id=\"first_element_for_eval\" style=\"visibility: hidden\">{$this->items[0]->clickEventHandler}</div>";
		}

		function _addJsControlItem( $id, $index, &$item )
		{
			echo "<script language=JavaScript>{$this->controlName}.addItem( \"$id\", \"$index\", \"{$item->clickEventHandler}\", \"{$item->dblClickEventHandler}\", \"{$item->objIdentifier}\" );\n\t</script>";
		}
		

	}

?>