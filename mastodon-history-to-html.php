<?php

/*
This script builds a single HTML from your Mastodon archive and "burns" the media into the single HTML.
This way you can read your own posts like a kind of diary.

The output.html needs an external CSS; download it and place both together. (This script tries to download it automatically)
https://www.w3schools.com/w3css/4/w3.css

This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <https://unlicense.org>
*/

$outboxes = "outboxes/"; // Place all your outbox.json, rename them manually to sort them in the correct date order
$media = "media/"; // Place your Media here. At the moment only JPG is supported; more coming soon.

// Error handling
if(!is_dir($outboxes)){mkdir($outboxes);}
if(!is_dir($outboxes)){mkdir($outboxes);}
if(!is_file("w3.css")){file_put_contents("w3.css",file_get_contents("https://www.w3schools.com/w3css/4/w3.css"));}

$files = array_slice(scandir($outboxes),2);
define("MEDIA", $media);

function beautify($file){

	$content = file_get_contents($file);
	$array = json_decode($content,true);unset($content);
	foreach($array['orderedItems'] as $contentpush){$content[] = $contentpush;}
	$content = array_reverse($content);
	array_values($content);

foreach($content as $posting){	
	if(!isset($posting['object']['inReplyTo'])){	
	if(isset($posting['to'][0]) && $posting['to'][0] == "https://www.w3.org/ns/activitystreams#Public")
	{
		if(!empty($posting['object']['content']))
		{
			$content = $posting['object']['content'];
			$date= $posting['published'];
			$date = strtotime($date);
			$date =  date("d.m.Y @ H:i:s",$date);
			
			if(!empty($posting['object']['attachment']))
			{
				
				foreach($posting['object']['attachment'] as $attachment)
				{
					$att1 ="";
					$att2 ="";
					$att_ok = "";
					
					if($attachment['mediaType'] == "image/jpeg")
					{
						$att1 = '<img src="';
						$att2 = '">';
						$pic = substr($attachment['url'],strrpos($attachment['url'],"/")+1);
						if(is_file(MEDIA.$pic)){
							$bild_get = file_get_contents(MEDIA.$pic);
							$bild_get = base64_encode($bild_get);
							$attachment['url'] = "data:image/jpg;base64,".$bild_get;
							//$attachment['url'] = $media.$pic;
							$att_ok = $att1.$attachment['url'].$att2;
							$content = $content.$att_ok;
						}
					}
				}
			}
			
			$htmlcollection[] = "<p>".$date."</p><p>".$content."</p>";
		}
	}
	
	}
}

$htmlcollection = array_reverse($htmlcollection);
$htmlcollection = array_unique($htmlcollection);
 $htmlcollection= array_values($htmlcollection);
return($htmlcollection);


}

foreach($files as $make)
{
	$postings = beautify($outboxes.$make);
	for($a=0;$a<count($postings);$a++)
	{
		$all[] = $postings[$a];
	}
}

if(empty($all))
{
	echo "There are no files in <b>/".$outboxes."</b>.";
	exit;
}

$all = array_unique($all);
$all = array_values($all);

for($a=0;$a<count($all);$a++)
{
	$all[$a] = "<p><i>Posting #".($a+1)."</i></p>".$all[$a];
}

$all = implode("<hr>",$all);

$header = '
<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mastodon Postings</title>
	<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, max-image-preview:none, notranslate" />
	<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📝</text></svg>">
    <meta name="description" content="Mastodon Postings">
	<link rel="stylesheet" href="w3.css">
	<style>
	a {color:blue;}
	hr {border:none;border-bottom:2px solid black;}
	html { font-family: "Trebuchet MS", sans-serif; 
	/*https://css-tricks.com/snippets/css/prevent-long-urls-from-breaking-out-of-container/*/
	/* These are technically the same, but use both */
  overflow-wrap: break-word;
  word-wrap: break-word;

  -ms-word-break: break-all;
  /* This is the dangerous one in WebKit, as it breaks things wherever */
  word-break: break-all;
  /* Instead use this non-standard one: */
  word-break: break-word;

  /* Adds a hyphen where the word breaks, if supported (No Blink) */
  -ms-hyphens: auto;
  -moz-hyphens: auto;
  -webkit-hyphens: auto;
  hyphens: auto;
	
	
	}
	img {max-width:300px;}
	</style>
</head>
<div class="w3-container">

';

$footer = "</div></html>";

$html =  $header.$all.$footer;;
echo $html;
file_put_contents("output.html",$html);
?>

