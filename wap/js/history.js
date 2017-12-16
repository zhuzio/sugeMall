$(function(){	
 var art_title = $(".z-location-k").val();
 $(".z-location-k").keyup(function(){
 	art_title = $(".z-location-k").val();
 });
 var art_url = document.URL;
 var history;
 var json="[";
 //json1是第一次注入cookie以后的第一个json，"此时还不是数组" 以点带面的处理
 var json1;
 var canAdd= true;
 //var json1=eval("({sitename:'dreamdu',sitedate:new Date(1980, 12, 17, 12, 0, 0)})");
 if(!$.cookie("history")){
 //第一次的时候需要初始化
 history = $.cookie("history","{title:\""+art_title+"\""+",url:\""+art_url+"\"}");
 }else {
 //已经存在
 history = $.cookie("history");
 json1 = eval("("+history+")");
 $(json1).each(function(){
  if(this.title==art_title){
  canAdd=false;
  return false;
  }
 });
 if(canAdd){
  $(json1).each(function(){
  json = json + "{\"title\":\""+this.title+"\",\"url\":\""+this.url+"\"},";
  })
  json = json + "{\"title\":\""+art_title+"\",\"url\":\""+art_url+"\"}]"; 
  $.cookie("history",json,{expires:1});
 }
 }
 });
