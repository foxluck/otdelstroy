<?php

class WidgetParamsModel extends DbModel
{
	protected $table = "WG_PARAM";
	
    public function getByWidget($widget_id)
    {
        $qc = $this->getQueryConstructor();
        $qc->fillSelect(array('WGP_NAME', 'WGP_VALUE'));
        return $qc->findByKey('WG_ID', $widget_id)->fetchAll('WGP_NAME', true);        
    }
}