<?php
/*
$dataread = array();
$fpoint = -1; //folder pointer
$ppoint	= 0; //point pointer
$lastfolder = ''; //lastfolder name
$fp = fopen("/Users/macrobio/Desktop/LOG.CSV" , "r" );
while (( $data = fgetcsv ( $fp , 0 , "," )) !== FALSE ) { // While more lines to read
//$line[0]foldername [1]pointname [2]lat. [3]lat. dir. [4]long [5]long. dir [6]alt.
//$dataread[$i] = ('foldername'=>$line[0],array('pointname'=>$line[1],array('lat'=>$line[2],'long'=>$line[4],'alt'=>$line[6])));

	if($data[0]==$lastfolder){ //point belong to same previous folder
		$ppoint++;
	}
	else{ // new folder
		$ppoint = 0;
		$fpoint++;
		$dataread[$fpoint]['fname'] 				= $data[0];// set new foldername
		$lastfolder =  $data[0];
		echo $dataread[$fpoint]['fname'] . "<br>";
	}
		$dataread[$fpoint][1][$ppoint]['pname']	= $data[1];// set new point name
		
		$lat = substr($data[2], 0,2) +substr($data[2], 2,8)/60;
		if(strcmp($data[3], 'S') == 0)
			$lat = $lat * (-1);
		$dataread[$fpoint][1][$ppoint]['lat']	= $lat;//set latitude
		
		$long = substr($data[4], 0,3) + substr($data[4], 3,9)/60;
		if(strcmp($data[5], 'W') == 0)
			$long = $long * (-1);
		$dataread[$fpoint][1][$ppoint]['long']	= $data[4];//set longitude
		
		$dataread[$fpoint][1][$ppoint]['elev'] 	= $data[6];//set altitude
		
		echo $lat .",".$long ."<br>";
}
fclose ($fp);
*/

//	private function distance($lat1,$long1,$elev1,$lat2,$long2,$elev2){
		
		$lat1 = 18.212990;
		$long1 = -67.147108;
		$elev1 = 10;
		
		$lat2 = 18.217408;
		$long2 = -67.152118;
		$elev2 = 100;
		
		$lat1 = deg2rad($lat1);
		$long1 = deg2rad($long1);
		$lat2 = deg2rad($lat2);
		$long2 = deg2rad($long2);
		
		//Major semi axe of Earth ellipsoid for GRS80
		$a = 6378137;
		//Minor semi axe of Earth ellipsoid for GRS80
		$b = 6356752.314140;
		//Flattening of ellipsoid for GRS80
		$f = 1/(298.257222101);

		$L = $long2-$long1; //longitude difference
		$u1 = atan((1-$f)*(tan($lat1))); //Reduced latitude1
		$u2 = atan((1-$f)*(tan($lat2))); //Reduced latitude2
		$sin_u1 = sin($u1);
		$cos_u1 = cos($u1);
		$sin_u2 = sin($u2);
		$cos_u2 = cos($u2);
		$lamda = $L; //first approximation
		$lamda_PI = 2*PI;
		
		$sin_sigma = 0;
		$cos_sigma = 0;
		$sigma = 0 ;
		$cos_Sq_Alpha = 0;
		$cos2sigma_m = 0;
		
		while (abs($lamda-$lamda_PI) >= 1E-12){
			$sin_lamda = sin($lamda);
			$cos_lamda = cos($lamda);
			$sin_sigma = sqrt((pow(($cos_u2*$sin_lamda), 2))+
					(pow(($cos_u1*$sin_u2-$sin_u1*$cos_u2*$cos_lamda), 2)));
			$cos_sigma = $sin_u1*$sin_u2+ $cos_u1*$cos_u2*$cos_lamda;
			$sigma = atan2($sin_sigma, $cos_sigma);
			$sin_alpha = $cos_u1*$cos_u2*$sin_lamda/$sin_sigma;
			//Trigonometric identity
			$cos_Sq_Alpha = 1 - pow($sin_alpha, 2);
			//-----------------
			$cos2sigma_m = $cos_sigma - 2*$sin_u1*$sin_u2/$cos_Sq_Alpha;
			
			if (is_nan($cos2sigma_m)){
				$cos2sigma_m = 0;
			}
			
			$C = $f/16*$cos_Sq_Alpha*(4+$f*(4-3*$cos_Sq_Alpha));
			$lamda_PI = $lamda;
			$lamda = $L+(1-$C)*$f*$sin_alpha*($sigma+$C*$sin_sigma*($cos2sigma_m+$C*$cos_sigma*(2*pow($cos2sigma_m, 2)-1)));
		}
		
		$uSq = $cos_Sq_Alpha*(pow($a, 2)-pow($b, 2))/pow($b, 2);
		$A = 1+$uSq/16384*(4096+$uSq*($uSq*(320-175*$uSq)-768));
		$B = $uSq/1024*(256+$uSq*($uSq*(74-47*$uSq)-128));
		$delta_sigma = $B*$sin_sigma*($cos2sigma_m+$B/4*($cos_sigma*(2*pow($cos2sigma_m, 2)-1)-$B/6*$cos2sigma_m*(4*pow($sin_sigma, 2)-3)*(4*pow($cos2sigma_m, 2)-3)));
		
		//return $b*$A*($sigma-$delta_sigma);
		
		//pythagoras theorem
		$x = $b*$A*($sigma-$delta_sigma);
		$y = $elev2 - $elev1;
		$z = sqrt(pow($x,2)+pow($y,2));

		//return $z;
		echo $z;
//	}



/*	
	private function getElevationDifference($elev1, $elev2){
		result = abs($elev2-$elev1);
		return result;
	}
*/






















//echo print_r($dataread);
//$foldernames = array();
//for($i=0;$i < count($dataread);$i++){
//	echo $dataread[$i]['fname'];
	//array_push($foldernames,'a');
//}
//print_r($foldernames);
/*	$dataread = array(
						array('foldername0',array(	
													array('pointname0','lat0','lon0','alt0'),
													array('pointname1','lat1','lon1','alt1'))),
					
						array('foldername1',array(	
													array('pointname0','lat0','lon0','alt0'),
													array('pointname1','lat1','lon1','alt1')))							
					);
													
													
													
													
	echo $dataread[0][0];//foldername0
	echo $dataread[0][1][0][0];//pointname0
	echo $dataread[0][1][0][1];//lat0
	echo $dataread[0][1][0][2];//lon0
	echo $dataread[0][1][0][3];//alt0
	
	//
	echo $dataread[0][1][1][0];//pointname1
	echo $dataread[0][1][1][1];//lat1
	echo $dataread[0][1][1][2];//lon1
	echo $dataread[0][1][1][3];//alt1

	echo "<br><br>";
	
	echo $dataread[1][0];//foldername1
	echo $dataread[1][1][0][0];//pointname0
	echo $dataread[1][1][0][1];//lat0
	echo $dataread[1][1][0][2];//lon0
	echo $dataread[1][1][0][3];//alt0
	
	//
	echo $dataread[1][1][1][0];//pointname1
	echo $dataread[1][1][1][1];//lat1
	echo $dataread[1][1][1][2];//lon1
	echo $dataread[1][1][1][3];//alt1

*/	
?>


