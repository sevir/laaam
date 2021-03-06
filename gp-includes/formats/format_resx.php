<?php

class GP_Format_ResX {

	var $name = '.NET Resource (.resx)';
	var $extension = 'resx.xml';

	var $exported = '';

	function line( $string, $prepend_tabs = 0 ) {
		$this->exported .= str_repeat( "\t", $prepend_tabs ) . "$string\n";
	}

	function res_header( $name, $value ) {
		$this->line( '<resheader name="'.$name.'">', 1 );
		$this->line( '<value>'.$value.'</value>', 2 );
		$this->line( '</resheader>', 1 );
	}

	function print_exported_file( $project, $locale, $translation_set, $entries ) {
		$this->exported = '';
		$this->line( '<?xml version="1.0" encoding="utf-8"?>' );
		$this->line( '<root>' );

		$this->add_schema_info();
		$this->add_schema_declaration();

		$this->res_header( 'resmimetype', 'text/microsoft-resx' );
		$this->res_header( 'version', '2.0' );
		$this->res_header( 'reader', 'System.Resources.ResXResourceReader, System.Windows.Forms, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089' );
		$this->res_header( 'writer', 'System.Resources.ResXResourceReader, System.Windows.Forms, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089' );
		foreach( $entries as $entry ) {
			if ( !preg_match( '/^[a-zA-Z0-9_]+$/', $entry->context ) ) {
				error_log( 'ResX Export: Bad Entry: '. $entry->context );
				continue;
			}

			if ( empty( $entry->translations ) || ! array_filter( $entry->translations ) )
				continue;

			$this->line( '<data name="' . $entry->context . '" xml:space="preserve">', 1 );
			$this->line( '<value>' . $this->escape( $entry->translations[0] ) . '</value>', 2 );
			if ( isset( $entry->extracted_comments ) && $entry->extracted_comments ) {
				$this->line( '<comment>' . $this->escape( $entry->extracted_comments ) . '</comment>', 2 );
			}
			$this->line( '</data>', 1 );
		}
		$this->line( '</root>' );
		return $this->exported;
	}

	function read_translations_from_file( $file_name, $project = null ) {
		if ( is_null( $project ) ) return false;
		$translations = $this->read_originals_from_file( $file_name );
		if ( !$translations ) return false;
		$originals = GP::$original->by_project_id( $project->id );
		$new_translations = new Translations;
		foreach( $translations->entries as $key => $entry ) {
			// we have been using read_originals_from_file to parse the file
			// so we need to swap singular and translation
			$entry->translations = array( $entry->singular );
			$entry->singular = null;
			foreach( $originals as $original ) {
				if ( $original->context == $entry->context ) {
					$entry->singular = $original->singular;
					break;
				}
			}
			if ( !$entry->singular ) {
				error_log( sprintf( __("Missing context %s in project #%d"), $entry->context, $project->id ) );
				continue;
			}

			$new_translations->add_entry( $entry );
		}
		return $new_translations;

	}

	function read_originals_from_file( $file_name ) {
		$errors = libxml_use_internal_errors( 'true' );
		$data = simplexml_load_string( file_get_contents( $file_name ) );
		libxml_use_internal_errors( $errors );
		if ( !is_object( $data ) ) return false;
		$entries = new Translations;
		foreach( $data->data as $string ) {
			$entry = new Translation_Entry();
			if ( isset( $string['type'] ) && gp_in( 'System.Resources.ResXFileRef', (string)$string['type'] ) ) {
				continue;
			}
			$entry->context = (string)$string['name'];
			$entry->singular = $this->unescape( (string)$string->value );
			if ( isset( $string->comment ) && $string->comment ) {
				$entry->extracted_comments = (string)$string->comment;
			}
			$entry->translations = array();
			$entries->add_entry( $entry );
		}
		return $entries;
	}


	function unescape( $string ) {
		return $string;
	}

	function escape( $string ) {
		$string = str_replace( array( '&', '<' ), array( '&amp;', '&lt;' ), $string );
		return $string;
	}


	function add_schema_info() {
		$this->line('<!-- 
		Microsoft ResX Schema 

		Version 2.0

		The primary goals of this format is to allow a simple XML format 
		that is mostly human readable. The generation and parsing of the 
		various data types are done through the TypeConverter classes 
		associated with the data types.

		Example:

		... ado.net/XML headers & schema ...
		<resheader name="resmimetype">text/microsoft-resx</resheader>
		<resheader name="version">2.0</resheader>
		<resheader name="reader">System.Resources.ResXResourceReader, System.Windows.Forms, ...</resheader>
		<resheader name="writer">System.Resources.ResXResourceWriter, System.Windows.Forms, ...</resheader>
		<data name="Name1"><value>this is my long string</value><comment>this is a comment</comment></data>
		<data name="Color1" type="System.Drawing.Color, System.Drawing">Blue</data>
		<data name="Bitmap1" mimetype="application/x-microsoft.net.object.binary.base64">
			<value>[base64 mime encoded serialized .NET Framework object]</value>
		</data>
		<data name="Icon1" type="System.Drawing.Icon, System.Drawing" mimetype="application/x-microsoft.net.object.bytearray.base64">
			<value>[base64 mime encoded string representing a byte array form of the .NET Framework object]</value>
			<comment>This is a comment</comment>
		</data>

		There are any number of "resheader" rows that contain simple 
		name/value pairs.
		
		Each data row contains a name, and value. The row also contains a 
		type or mimetype. Type corresponds to a .NET class that support 
		text/value conversion through the TypeConverter architecture. 
		Classes that don\'t support this are serialized and stored with the 
		mimetype set.
		
		The mimetype is used for serialized objects, and tells the 
		ResXResourceReader how to depersist the object. This is currently not 
		extensible. For a given mimetype the value must be set accordingly:
		
		Note - application/x-microsoft.net.object.binary.base64 is the format 
		that the ResXResourceWriter will generate, however the reader can 
		read any of the formats listed below.
		
		mimetype: application/x-microsoft.net.object.binary.base64
		value   : The object must be serialized with 
				: System.Runtime.Serialization.Formatters.Binary.BinaryFormatter
				: and then encoded with base64 encoding.
		
		mimetype: application/x-microsoft.net.object.soap.base64
		value   : The object must be serialized with 
				: System.Runtime.Serialization.Formatters.Soap.SoapFormatter
				: and then encoded with base64 encoding.

		mimetype: application/x-microsoft.net.object.bytearray.base64
		value   : The object must be serialized into a byte array 
				: using a System.ComponentModel.TypeConverter
				: and then encoded with base64 encoding.
	-->', 1 );
	}

	function add_schema_declaration() {
		$this->line( '<xsd:schema id="root" xmlns="" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:msdata="urn:schemas-microsoft-com:xml-msdata">', 1 );
		$this->line( '<xsd:import namespace="http://www.w3.org/XML/1998/namespace" />', 2 );
		$this->line( '<xsd:element name="root" msdata:IsDataSet="true">', 2 );
		$this->line( '<xsd:complexType>', 3 );
		$this->line( '<xsd:choice maxOccurs="unbounded">', 4 );

		$this->line( '<xsd:element name="metadata">', 5 );
		$this->line( '<xsd:complexType>', 6 );
		$this->line( '<xsd:sequence>', 7 );
		$this->line( '<xsd:element name="value" type="xsd:string" minOccurs="0" />', 8 );
		$this->line( '</xsd:sequence>', 7 );
		$this->line( '<xsd:attribute name="name" use="required" type="xsd:string" />', 7 );
		$this->line( '<xsd:attribute name="type" type="xsd:string" />', 7 );
		$this->line( '<xsd:attribute name="mimetype" type="xsd:string" />', 7 );
		$this->line( '<xsd:attribute ref="xml:space" />', 7 );
		$this->line( '</xsd:complexType>', 6 );
		$this->line( '</xsd:element>', 5 );

		$this->line( '<xsd:element name="assembly">', 5 );
		$this->line( '<xsd:complexType>', 6 );
		$this->line( '<xsd:attribute name="alias" type="xsd:string" />', 7 );
		$this->line( '<xsd:attribute name="name" type="xsd:string" />', 7 );
		$this->line( '</xsd:complexType>', 6 );
		$this->line( '</xsd:element>', 5 );

		$this->line( '<xsd:element name="data">', 5 );
		$this->line( '<xsd:complexType>', 6 );
		$this->line( '<xsd:sequence>', 7 );
		$this->line( '<xsd:element name="value" type="xsd:string" minOccurs="0" msdata:Ordinal="1" />', 8 );
		$this->line( '<xsd:element name="comment" type="xsd:string" minOccurs="0" msdata:Ordinal="2" />', 8 );
		$this->line( '</xsd:sequence>', 7 );
		$this->line( '<xsd:attribute name="name" type="xsd:string" use="required" msdata:Ordinal="1" />', 7 );
		$this->line( '<xsd:attribute name="type" type="xsd:string" msdata:Ordinal="3" />', 7 );
		$this->line( '<xsd:attribute name="mimetype" type="xsd:string" msdata:Ordinal="4" />', 7 );
		$this->line( '<xsd:attribute ref="xml:space" />', 7 );
		$this->line( '</xsd:complexType>', 6 );
		$this->line( '</xsd:element>', 5 );

		$this->line( '<xsd:element name="resheader">', 5 );
		$this->line( '<xsd:complexType>', 6 );
		$this->line( '<xsd:sequence>', 7 );
		$this->line( '<xsd:element name="value" type="xsd:string" minOccurs="0" msdata:Ordinal="1" />', 8 );
		$this->line( '</xsd:sequence>', 7 );
		$this->line( '<xsd:attribute name="name" type="xsd:string" use="required" />', 7 );
		$this->line( '</xsd:complexType>', 6 );
		$this->line( '</xsd:element>', 5 );

		$this->line( '</xsd:choice>', 4 );
		$this->line( '</xsd:complexType>', 3 );
		$this->line( '</xsd:element>', 2 );
		$this->line( '</xsd:schema>', 1 );
	}

}

GP::$formats['resx'] = new GP_Format_ResX;