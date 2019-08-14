<?php
$installer = $this;
$installer->startSetup();
$dataflowData = array(
    array(
        'name'         => 'Dotsquares - Export Newsletter Subscribers',
		'actions_xml'  => '<action type="dotsquares_exportprofiles/convert_adapter_subscribers" method="load">'."\r\n".'<var name="store"><![CDATA[0]]></var>'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dotsquares_exportprofiles/convert_parser_subscribers" method="unparse">'."\r\n".'<var name="store"><![CDATA[0]]></var>'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dataflow/convert_mapper_column" method="map">'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dataflow/convert_parser_csv" method="unparse">'."\r\n".'<var name="delimiter"><![CDATA[,]]></var>'."\r\n".'<var name="enclose"><![CDATA["]]></var>'."\r\n".'<var name="fieldnames">true</var>'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dataflow/convert_adapter_io" method="save">'."\r\n".'<var name="type">file</var>'."\r\n".'<var name="path">var/export</var>'."\r\n".'<var name="filename"><![CDATA[export_newsletter_subscribers.csv]]></var>'."\r\n".'</action>',
		'store_id'     => 0
    ),
	array(
        'name'         => 'Dotsquares - Import Newsletter Subscribers',
		'actions_xml'  => '<action type="dataflow/convert_adapter_io" method="load">'."\r\n".'<var name="type">file</var>'."\r\n".'<var name="path">var/import</var>'."\r\n".'<var name="filename"><![CDATA[import_newsletter_subscribers.csv]]></var>'."\r\n".'<var name="format"><![CDATA[csv]]></var>'."\r\n".'</action>'."\r\n".'<action type="dataflow/convert_parser_csv" method="parse">'."\r\n".'<var name="delimiter"><![CDATA[,]]></var>'."\r\n".'<var name="enclose"><![CDATA["]]></var>'."\r\n".'<var name="fieldnames">false</var>'."\r\n".'<var name="store"><![CDATA[0]]></var>'."\r\n".'<var name="adapter">dotsquares_exportprofiles/convert_adapter_subscribers</var>'."\r\n".'<var name="method">saveRow</var>'."\r\n".'</action>',
		'store_id'     => 0
    ),
);

foreach ($dataflowData as $bind) {
    Mage::getModel('dataflow/profile')->setData($bind)->save();
}

$installer->endSetup();