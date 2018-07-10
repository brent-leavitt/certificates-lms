<?php		

			echo "I'm here!";

		function print_pre($val){
			echo "<pre style='border: 2px solid green; background: yellow; padding: 20px; width: 300px;'>";
			var_dump($val);
			echo "</pre>";
		}

		$sName = 'Michael Montgomery McClurry';

		
		$sPost = array('first_name' => null, 'last_name' => null);

		if( empty( $sPost['first_name'] ) ){
			$sPost['first_name'] = substr( $sName, 0, strrpos( $sName, ' ' ) ); //This should return everything except the last name. 
		}
		
		if( empty( $sPost['last_name'] ) ){
			$sPost['last_name'] = substr( strrchr( $sName, ' ' ), 1 );
		}
		
		print_pre($sPost);
		

?>