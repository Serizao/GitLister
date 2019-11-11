<?php


function getUrl($url){
  return file_get_contents($url);
}

function fileOrDirectory($input,$baseFolder='/'){
  libxml_use_internal_errors(false);
  $html = new DOMDocument();
  @$html->loadHTML($input);
  $xpath = new DOMXPath($html);
  $tags = $xpath->query("//tr[contains(@class, 'js-navigation-item')]");
  $data = array();
  $compteur = 0;
  foreach ($tags as $tag) {
      $innerHTML = '';
      $children = $tag->childNodes;
      foreach ($children as $child) {
          $tmp_doc = new DOMDocument();
          $tmp_doc->appendChild($tmp_doc->importNode($child,true));
          $innerHTML.= $tmp_doc->saveHTML();
      }

        $html = trim($innerHTML);
      // title of object
      $titleRegex = '/<span class="css-truncate css-truncate-target"><a class="js-navigation-open" title="([\W\w]{1,})" id/m';
      $titleRegex2 = '/<span class="css-truncate css-truncate-target"><a class="js-navigation-open" title="[\W\w]{1,}" id="[\W\w]{1,}">([\W\w]{1,})<\/span>([\W\w]{1,})<\/a><\/span>/m';

      preg_match($titleRegex,$html ,$title);
      if(isset($title[1]) and strpos($title[1],"This path skips through empty")===false) $data[$compteur]['title'] = $title[1];
      if(isset($title[1]) and strpos($title[1],"This path skips through empty")!==false){
        preg_match($titleRegex2,$html ,$title);
        $data[$compteur]['title'] = $title[1].$title[2];
      }
      //link of object
      $linkRegex = '/<span class="css-truncate css-truncate-target"><a class="js-navigation-open" title="[\W\w]{1,}" id="[\W\w]{1,}" href="([\w\W]{1,})">[\w\W]{1,}<\/a><\/span>/m';
      $linkRegex2 = '/<span class="css-truncate css-truncate-target"><a class="js-navigation-open" title="[\W\w]{1,}" id="[\W\w]{1,}" href="([\w\W]{1,})"><span class="simplified-path">[\w\W]{1,}<\/span>[\w\W]{1,}<\/a><\/span>/m';

      preg_match($linkRegex, $html ,$link);
      if(isset($link[1]) and strpos($link[1],"simplified-path")===false) $data[$compteur]['link'] = $link[1];
      if(isset($link[1]) and strpos($link[1],"simplified-path")!==false){
        preg_match($linkRegex2, $html ,$link);
        if(isset($link[1]) and strpos($link[1],"simplified-path")===false) $data[$compteur]['link'] = $link[1];
      }
      if(strpos($html, 'aria-label="file"')){
        $data[$compteur]['type'] = 'file';
        $data[$compteur]['path'] = $baseFolder.$data[$compteur]['title'];
      } elseif (strpos($html, 'aria-label="directory"')) {
        $data[$compteur]['type'] = 'directory';
        $data[$compteur]['path'] = $baseFolder.$data[$compteur]['title'];
      }
      
      $compteur++;
  }
  return $data;
}

function treeDirectory($result){
  for($i=0;$i<count($result);$i++){
    if(isset($result[$i]['type']) and $result[$i]['type'] == "directory"){
      $a = fileOrDirectory(getUrl('https://github.com'.$result[$i]["link"]),$result[$i]["path"].'/');
      $result[$i]["sub"] = treeDirectory($a);
    }
  }
  return $result;
}

function extractPath($data,$newData=array()){
  for($i=0;$i<count($data);$i++){
  
    if(isset($data[$i]['sub'])){
      $temp = extractPath($data[$i]['sub'],$newData);
      if(count($temp)>0)  $newData = array_merge($newData,$temp);
    }
    if(!empty($data[$i]['path'])) $newData[] = $data[$i]['path'];
   
  }
  return $newData;
}


$directory = 1;
if(strstr( $argv[1], '--repo=' )) $url = explode('=',$argv[1])[1];
else{
  echo "Repo paramater is missing";
  exit;
}
$data = getUrl($url);
$result = fileOrDirectory($data);
if($directory){
  echo implode(PHP_EOL,extractPath(treeDirectory($result)));
}
