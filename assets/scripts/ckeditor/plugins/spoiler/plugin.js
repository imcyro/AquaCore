﻿(function(){CKEDITOR.config.spoilerContentAllowedContent="";CKEDITOR.config.magicline_tabuList=CKEDITOR.config.magicline_tabuList||[];CKEDITOR.config.magicline_tabuList.push("cke-spoiler-wrapper");CKEDITOR.plugins.add("spoiler",{lang:["en","pt-BR"],requires:["widget"],icons:"spoiler",init:function(c){c.widgets.add("spoiler",{allowedContent:"div[!cke-spoiler-wrapper](!spoiler-wrapper);div(!spoiler-title);div(!spoiler-content);",requiredContent:"div(spoiler-wrapper)",button:c.lang.spoiler.buttonTitle,
toolbar:"spoiler,0",draggable:!1,inline:!1,template:'<div class="spoiler-wrapper" cke-spoiler-wrapper="1"><div class="spoiler-title">'+c.lang.spoiler.title+'</div><div class="spoiler-content"></div></div>',editables:{content:{selector:"div.spoiler-content",allowedContent:c.config.spoilerContentAllowedContent,pathName:"spoiler"}},parts:{title:"div.spoiler-title",content:"div.spoiler-content"},init:function(){var a=this;this.parts.title.on("click",function(b){b.data.preventDefault();a.parts.content.hasClass("spoiler-hidden")?
(this.removeClass("spoiler-hidden"),a.parts.content.removeClass("spoiler-hidden"),a.parts.content.removeAttribute("tabIndex")):(this.addClass("spoiler-hidden"),a.parts.content.addClass("spoiler-hidden"),a.parts.content.setAttribute("tabIndex","-1"))})},upcast:function(a){if("div"===a.name&&a.hasClass("spoiler-wrapper")){var b=a.getFirst();if(b&&"div"===b.name&&b.hasClass("spoiler-title")&&b.next&&"div"===b.next.name&&b.next.hasClass("spoiler-content"))return a.attributes["cke-spoiler-wrapper"]="1",
a.attributes.contenteditable="false",!0}return!1},downcast:function(a){delete a.attributes.contenteditable;delete a.attributes["cke-spoiler-wrapper"]}})},onLoad:function(){CKEDITOR.addCss(".spoiler-wrapper {position: relative;padding: .2em 10px;margin: 1.4em 0 1em;border: 1px solid #a6a6a6;outline: none !important;border-radius: 3px;background: #ededed;-moz-box-shadow: 0 2px 2px rgba(0, 0, 0, .3) inset;-webkit-box-shadow: 0 2px 2px rgba(0, 0, 0, .3) inset;box-shadow: 0 2px 2px rgba(0, 0, 0, .3) inset;}.spoiler-title {position: absolute;display: inline-block;top: -1em;left: 1em;outline: none !important;border: 1px solid #a6a6a6;padding: .3em 10px;font-size: .80em;font-weight: bold;border-radius: 4px;cursor: pointer;text-shadow: 1px 1px 0 0 white;background: #ffffff;background: -moz-linear-gradient(top,  #ffffff 0%, #e4e4e4 100%);background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(100%,#e4e4e4));background: -webkit-linear-gradient(top,  #ffffff 0%,#e4e4e4 100%);background: -o-linear-gradient(top,  #ffffff 0%,#e4e4e4 100%);background: -ms-linear-gradient(top,  #ffffff 0%,#e4e4e4 100%);background: linear-gradient(to bottom,  #ffffff 0%,#e4e4e4 100%);filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e4e4e4',GradientType=0 );}.spoiler-title:hover {-moz-box-shadow: 0 0 1px rgba(0, 0, 0, .3) inset;-webkit-box-shadow: 0 0 1px rgba(0, 0, 0, .3) inset;box-shadow: 0 0 1px rgba(0, 0, 0, .3) inset;background: #f2f2f2;background: -moz-linear-gradient(top,  #f2f2f2 0%, #cccccc 100%);background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f2f2f2), color-stop(100%,#cccccc));background: -webkit-linear-gradient(top,  #f2f2f2 0%,#cccccc 100%);background: -o-linear-gradient(top,  #f2f2f2 0%,#cccccc 100%);background: -ms-linear-gradient(top,  #f2f2f2 0%,#cccccc 100%);background: linear-gradient(to bottom,  #f2f2f2 0%,#cccccc 100%);filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f2f2f2', endColorstr='#cccccc',GradientType=0 );}.spoiler-content {margin: 1.2em 10px .5em;min-height: 1em;}.spoiler-content.spoiler-hidden {display: none;}")}})})();