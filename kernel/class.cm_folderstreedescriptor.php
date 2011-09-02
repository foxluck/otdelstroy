<?php
	class CM_FoldersTreeDescriptor extends FoldersTreeDescriptor
	{
		function CM_FoldersTreeDescriptor( )
		{
			$this->folderDescriptor = new treeFolderTableDescriptor( 'CFOLDER', 'CF_ID', 'CF_NAME', 'CF_ID_PARENT', 'CF_STATUS' );
			$this->documentDescriptor = new treeDocumentsTableDescriptor( 'CONTACT', 'C_ID', 'C_STATUS', 'C_MODIFYUSERNAME' );
		}
	}

	$cm_TreeFoldersDescriptor = new CM_FoldersTreeDescriptor( );

?>