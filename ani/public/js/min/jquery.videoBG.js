!function(e){e.fn.videoBG=function(t,i){var i={};if("object"==typeof t)i=e.extend({},e.fn.videoBG.defaults,t);else{if(t)return e(t).videoBG(i);i=e.fn.videoBG.defaults}var s=e(this);if(s.length){"static"!=s.css("position")&&s.css("position")||s.css("position","relative"),0==i.width&&(i.width=s.width()),0==i.height&&(i.height=s.height());var o=e.fn.videoBG.wrapper();o.height(i.height).width(i.width),i.textReplacement?(i.scale=!0,s.width(i.width).height(i.height).css("text-indent","-9999px")):o.css("z-index",i.zIndex+1);var n=e.fn.videoBG.video(i);return i.scale&&(o.height(i.height).width(i.width),n.height(i.height).width(i.width)),s.append(o.append(n)),n.find("video")[0]}},e.fn.videoBG.setFullscreen=function(t){var i=e(window).width(),s=e(window).height();if(t.css("min-height",0).css("min-width",0),t.parent().width(i).height(s),i/s>t.aspectRatio){t.width(i).height("auto");var o=t.height(),n=(o-s)/2;0>n&&(n=0),t.css("top",-n)}else{t.width("auto").height(s);var d=t.width(),n=(d-i)/2;if(0>n&&(n=0),t.css("left",-n),0===n){setTimeout(function(){e.fn.videoBG.setFullscreen(t)},500)}}e("body > .videoBG_wrapper").width(i).height(s)},e.fn.videoBG.video=function(t){e("html, body").scrollTop(-1);var i=e("<div/>");i.addClass("videoBG").css("position",t.position).css("z-index",t.zIndex).css("top",0).css("left",0).css("height",t.height).css("width",t.width).css("opacity",t.opacity).css("overflow","hidden");var s=e("<video/>");if(s.css("position","absolute").css("z-index",t.zIndex).attr("poster",t.poster).css("top",0).css("left",0).css("min-width","100%").css("min-height","100%"),t.autoplay&&s.attr("autoplay",t.autoplay),t.fullscreen){s.bind("canplay",function(){s.aspectRatio=s.width()/s.height(),e.fn.videoBG.setFullscreen(s)});var o;e(window).resize(function(){clearTimeout(o),o=setTimeout(function(){e.fn.videoBG.setFullscreen(s)},100)}),e.fn.videoBG.setFullscreen(s)}var n=s[0];t.loop&&(loops_left=t.loop,s.bind("ended",function(){loops_left&&n.play(),loops_left!==!0&&loops_left--})),s.bind("canplay",function(){t.autoplay&&n.play()}),e.fn.videoBG.supportsVideo()&&(e.fn.videoBG.supportType("webm")?s.attr("src",t.webm):e.fn.videoBG.supportType("mp4")?s.attr("src",t.mp4):s.attr("src",t.ogv));var d=e("<img/>");return d.attr("src",t.poster).css("position","absolute").css("z-index",t.zIndex).css("top",0).css("left",0).css("min-width","100%").css("min-height","100%"),i.html(e.fn.videoBG.supportsVideo()?s:d),t.textReplacement&&(i.css("min-height",1).css("min-width",1),s.css("min-height",1).css("min-width",1),d.css("min-height",1).css("min-width",1),i.height(t.height).width(t.width),s.height(t.height).width(t.width),d.height(t.height).width(t.width)),e.fn.videoBG.supportsVideo()&&n.play(),i},e.fn.videoBG.supportsVideo=function(){return document.createElement("video").canPlayType},e.fn.videoBG.supportType=function(t){if(!e.fn.videoBG.supportsVideo())return!1;var i=document.createElement("video");switch(t){case"webm":return i.canPlayType('video/webm; codecs="vp8, vorbis"');case"mp4":return i.canPlayType('video/mp4; codecs="avc1.42E01E, mp4a.40.2"');case"ogv":return i.canPlayType('video/ogg; codecs="theora, vorbis"')}return!1},e.fn.videoBG.wrapper=function(){var t=e("<div/>");return t.addClass("videoBG_wrapper").css("position","absolute").css("top",0).css("left",0),t},e.fn.videoBG.defaults={mp4:"",ogv:"",webm:"",poster:"",autoplay:!0,loop:!0,scale:!1,position:"absolute",opacity:1,textReplacement:!1,zIndex:0,width:0,height:0,fullscreen:!1,imgFallback:!0}}(jQuery);