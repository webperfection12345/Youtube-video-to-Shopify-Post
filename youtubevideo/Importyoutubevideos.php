<?php

    error_reporting(E_ERROR | E_PARSE);

	$JsonVideo = file_get_contents("https://youtube.googleapis.com/youtube/v3/activities?part=snippet%2CcontentDetails&channelId=channelID=date&maxResults=100&key=AddKey");
	$Json_video = json_decode($JsonVideo);

	$videoid = [];
	$maincontent = [];
	foreach($Json_video as $key11 =>$videocon){
		foreach($videocon as $key11 =>$newvideo): 
	        $videoid[] = $newvideo->contentDetails->upload->videoId;
	        if(isset($newvideo->snippet->thumbnails->maxres)){
	        	$turl = $newvideo->snippet->thumbnails->maxres->url;
	        }
	        else{
	        	$turl = $newvideo->snippet->thumbnails->high->url;
	        }
	        $maincontent[] = array('title' => $newvideo->snippet->title, 'description' => $newvideo->snippet->description,'image' => $turl,'vid' => $newvideo->contentDetails->upload->videoId);
		endforeach;
	}
    
    $vicid = [];
	foreach($videoid as  $key => $vcont){
	    $JsonViews = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=statistics&id=$vcont&key=AddKey");
	    $Json_Views = json_decode($JsonViews);
	    foreach($Json_Views->items as $keys => $valuess):
            $vicid[] = array('vid' => $valuess->id, 'vcontnt' => $valuess->statistics->viewCount); 
	    endforeach;
	}

	$arrModuleIndex  =  [];
	foreach($maincontent as $key => $data){
	    $arrModuleIndex[$data['vid']] = $key;
	}
	foreach($vicid as $data){
	    $maincontent[$arrModuleIndex[$data['vid']]]['views'] = $data['vcontnt'];
	}
     
    
	$maincontent = array_values(array_filter($maincontent));
    $maincontent = array_reverse($maincontent);

       array_shift($maincontent);
       array_shift($maincontent);
    

    function callAPI($method, $url, $data){
	   $curl = curl_init();
	   switch ($method){
	      case "POST":
	         curl_setopt($curl, CURLOPT_POST, 1);
	         if ($data)
	            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	         break;
	      case "PUT":
	         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	         if ($data)
	            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                              
	         break;
	      default:
	         if ($data)
	            $url = sprintf("%s?%s", $url, http_build_query($data));
	   }
	  
	   curl_setopt($curl, CURLOPT_URL, $url);
	   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	      'Content-Type: application/json',
	      'X-Shopify-Access-Token: Access Token'
	   ));
	   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	   
	   $result = curl_exec($curl);
	   if(!$result){die("Connection Failure");}
	   curl_close($curl);
	   return $result;
	}

    
    $ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, 'https://the-peoples-chemist.myshopify.com/admin/api/2022-10/blogs/85481881778/articles.json');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


	$headers = array();
	$headers[] = 'X-Shopify-Access-Token: Access Token';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);
	$array = json_decode($result, true);
	
  

  $match_title = array();
  $match_articles_arr = array();
	foreach($array as $array_val){
	    foreach($array_val as $blog_post){
		    $match_title[] =  $blog_post['title']; 
		    $match_articles_arr[] =  array('id' => $blog_post['id'], 'title' => $blog_post['title']);    
		}
    }

    $arrModuleIndex1  =  [];
	foreach($maincontent as $key => $data){
	    $arrModuleIndex1[$data['title']] = $key;
	}
	foreach($match_articles_arr as $data){
	    $maincontent[$arrModuleIndex1[$data['title']]]['id'] = $data['id'];
	}
     

    foreach( $maincontent as $key => $post_item ) :
    	
		$wp_title  = $post_item['title'];
	    $wp_content = $post_item['description'];
        $image_url  = $post_item['image'];
        $videoviews = $post_item['views'];
        $article_id = $post_item['id'];

        $videourl =  "https://www.youtube.com/embed/".$post_item['vid'];
	    if(in_array($post_item['title'], $match_title)){

	    	    $ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, 'https://the-peoples-chemist.myshopify.com/admin/api/2022-10/articles/'.$article_id.'/metafields.json');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


				$headers = array();
				$headers[] = 'X-Shopify-Access-Token: Access Token';
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$result = curl_exec($ch);
				if (curl_errno($ch)) {
					echo 'Error:' . curl_error($ch);
				}
				curl_close($ch);
				$array1 = json_decode($result, true);
               
			    $updateblogsss = ["metafield" => 
			            [
		    	    	    "id" => $array1['metafields']['1']['id'],
					        "value" => $videoviews,
					        "type" => "single_line_text_field",
						]
			    ]; 
		   $update_datass = callAPI('PUT', 'https://the-peoples-chemist.myshopify.com/admin/api/2022-10/articles/'.$article_id.'/metafields/'.$array1['metafields']['1']['id'].'.json', json_encode($updateblogsss));
		     if($update_datass){

                 echo "Successfully Update video Data ---(". $wp_title.")<br>";
		     }
		     else{
		     	echo "Somthink Wrong";
		     }
			 
		}
		else{
			
		    $insertblog = ["article" => [
		            "title"=> $wp_title,
					"body_html" => $wp_content,
					"image" => [
						"src" => $image_url,
						"alt" => $wp_title
					],
					"metafields" => [[
						"key" => "youtubelink",
				        "value" => $videourl,
				        "type" => "url",
				        "namespace" => "custom"
					],
					[
						"key" => "views",
				        "value" => $videoviews,
				        "type" => "single_line_text_field",
				        "namespace" => "custom"
					]
				   ],
					"published" =>  true
				]
		    ]; 
		      
            
		    $insert_data_blog = callAPI('POST', 'https://the-peoples-chemist.myshopify.com/admin/api/2022-10/blogs/85481881778/articles.json', json_encode($insertblog));
		    if($insert_data_blog){
              echo "Successfully Create New video ---(". $wp_title.")<br>";
		    }
		    else{
               echo "Somthink Wrong";
		    }
		}

   endforeach;