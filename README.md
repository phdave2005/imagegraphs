/*
 * gplusTip - A tooltip populated by data pulled from the Google+ API
 * Copyright 2018 David Partyka
 * www.cssburner.com
 *
 * Version 1.0
 *
 * The gplusTip jQuery plug-in is dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
 
   The gplusTip tooltip is a pure jQuery-based tooltip which first makes a Google+ API request to pull data, and then uses this data to populate the tooltip before rendering it. As such, you must add your Google+ API key to replace the comment (in the accompanying JS file) that says " YOUR GOOGLE+ KEY GOES HERE "; gplusTip is enabled to use all default values by simply invoking it as a callback as follows:
    
    $(selector).on("mouseenter", function () {
        $(this).gplusTip(arg);
    }); // other events, e.g., click can also be used
    
where the minimal object (arg) that is passed is 
    
    arg = {
      event: e,
      t: this
    };
 
The selector must pass an attribute: **data-post_id**
where this attribute is the Google+ post id, e.g. <div data-post_id="63P2Gs8Bnsn"></div>

which can be found as the 11-character string at the end of the URL for the post (you can click the arrow icon in upper right of panel on the post to go to the URL for that post). 
 
The default options can be overwritten by also passing "user_defined_settings":

     arg = {
      event: e,
      t: this,
      user_defined_settings: {
          animationOnDestroy: false,
          arrowOffset: 3,
          background: '#cdcdcd',
          createCallback: false,
          delay: 250,
          destroyCallback: false,
          destroyOnMouseleave: true,
          limit: 5,
          maxWidth: .25,
          type: 'posts',
          verbose: false
      }
    };
   
    
The supported values of each user defined setting is as follows:

animationOnDestroy:
    false: The gplusTip container will simply be removed from the DOM
    'fadeOut': the container will fadeOut over 500ms, and then be removed
    'slideUp': the container will slideUp over 500ms, and then be removed

arrowOffset:
    <number>: The number of pixel to offset the container and arrow from the event which calls gplusTip() in callback
  
background:
    <color> or <image>: The background for the container
  
createCallback:
    <function>: A function to call right after the tooltip is rendered
  
delay:
    <number>: The milliseconds to delay before rendering the tooltip

destroyCallback:
    <function>: A function to call right after the tooltip is removed from the DOM

destroyOnMouseleave:
    <boolean>: If tooltip is moused over, it will be destroyed on mouseleave (if set to true, otherwise not destroyed)

limit:
  <number>: Set the number of returned records to display in the tooltip, otherwise 5 records will be included in output (-1 for all)

maxWidth:
    <number>: relative to window width, where window width = 1, will be constrained to be between .15 and .45

type
    <string>: Either 'posts' or 'comments' (comments requires the 11-character id for the specific post)

verbose:
    <boolean>: If set to true, additional data, such as publish date, url, statistics, and description will be used to populate the tooltip. If set to false, the minimal data (e.g., title for the 'type=title' option or comments

