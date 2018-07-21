(function($) {
    $.fn.gplusTip = function(arg) {
        var default_settings = {
                animationOnDestroy: false,
                arrowOffset: 3,
                background: '#cdcdcd',
                createCallback: false,
                delay: 250,
                destroyCallback: false,
                destroyOnMouseleave: true,
                filterPosts: [],
                hiddenSections: [],
                limit: 5,
                maxWidth: .25
            },
            settings = arg.user_defined_settings ? $.extend(default_settings, arg.user_defined_settings) : default_settings,
            Id = $(arg.t).attr("data-gplus_id"),
            gp_key = 'AIzaSyDPqCQOuK6lrVu-MwWzJVon3HH8lnG4xJ4',
            w = $(window),
            ww = w.width(),
            wh = w.height(),
            font_awesome_version;

        // Constrain size of container
        if (settings.maxWidth > .45) {
            settings.maxWidth = .45;
        } else {
            if (settings.maxWidth < .15) {
                settings.maxWidth = .15;
            }
        }

        if ($("#gp_container").length) {
            removeGplustip();
        }
        
        $("link").each(function() {
            var href = $(this)[0].href;
            if (href.match(/font-awesome-4\./)) {
                font_awesome_version = 4;
                return false;
            } else {
                if (href.match(/fontawesome-free-5\./)) {
                    font_awesome_version = 5;
                    return false;
                }
            }
        });

        var maxWidth = settings.maxWidth * ww,
            apiURL = 'https://www.googleapis.com/plus/v1/people/' + Id + '/activities/public?key=' + gp_key;// : 'https://www.googleapis.com/plus/v1/activities/' + Id + '/comments?key=' + gp_key;

        $.ajax({
            type: "GET",
            url: apiURL,
            data: {},
            success: function(response) {
                var i,
                    items = response.items,
                    itemsLength = items.length,
                    len = (itemsLength <= settings.limit) ? itemsLength : settings.limit,
                    formatDate = function(publishedAt) {
                        var date = new Date(publishedAt).toString(),
                            splitDate,
                            formattedDate;

                        splitDate = date.split(" ");
                        splitDate.shift();
                        splitDate.pop();
                        formattedDate = splitDate.join(" ");
                        formattedDate = formattedDate.slice(0, formattedDate.indexOf(" ("));

                        return formattedDate;
                    },
                    ARG = [],
                    ARG_object,
                    stats_object;
                
                for (i = 0; i < len; i++) {
                    if (items[i].kind && (items[i].kind === 'plus#activity')) {
                        if (!settings.filterPosts.length || (settings.filterPosts.indexOf(items[i].url.slice(items[i].url.lastIndexOf("/") + 1)) > -1)) {
                            ARG_object = {};
                            stats_object = {};
                            if (settings.hiddenSections.indexOf("title") === -1) {
                                ARG_object.Title = items[i]['title'];
                            }
                            if (settings.hiddenSections.indexOf("published") === -1) {
                                ARG_object.Published_At = formatDate(items[i]['published']);
                            }
                            if (settings.hiddenSections.indexOf("content") === -1) {
                                ARG_object.Description = items[i]['object']['content'];
                            }
                            if (settings.hiddenSections.indexOf("url") === -1) {
                                ARG_object.Link = items[i]['url'];
                            }
                            if (settings.hiddenSections.indexOf("replies") === -1) {
                                stats_object.Replies = items[i]['object']['replies']['totalItems'];
                            }
                            if (settings.hiddenSections.indexOf("plusones") === -1) {
                                stats_object.Plus_Ones = items[i]['object']['plusoners']['totalItems'];
                            }
                            if (settings.hiddenSections.indexOf("resharers") === -1) {
                                stats_object.Reshares = items[i]['object']['resharers']['totalItems'];
                            }
                            if (!$.isEmptyObject(stats_object)) {
                                ARG_object.Statistics = stats_object;
                            }
                            ARG.push(ARG_object);
                        }
                    }
                }                

                if (ARG.length) {
                    populateContainer(ARG);
                }
                
            },
            error: function(jqXHR, textStatus, errorThrown) {
                populateContainer();
            }
        });

        function isSupportedAnimation(type) {
            var supported = ['fadeOut', 'slideUp'];
            return supported.indexOf(type) > -1;
        }

        function populateContainer(ARG) {
            var i, j, key,
                inner = '<h2><b>Posts</b></h2>',
                len = ARG.length,
                list,
                obj,
                stats_symbol_builder = function(key) {
                    var str;
                    if (font_awesome_version) {
                        var map = {
                            v4: {
                                Replies: "fa fa-comment",
                                Plus_Ones: "fa fa-plus",
                                Reshares: "fa fa-share"
                            },
                            v5: {
                                Replies: "fas fa-comment",
                                Plus_Ones: "fas fa-plus",
                                Reshares: "fas fa-share"
                            }
                        };

                        str = '<i class="' + map["v" + font_awesome_version][key] + '"></i>';
                    }

                    return str;
                },
                html_iterator = {
                    html: function(key, val) {
                        return '<b>' + key.replace(/_/g, " ") + ':</b> ' + val;
                    },
                    link: function(key, val) {
                        return '<b>' + key.replace(/_/g, " ") + ':</b> <a href="' + val + '" target="_blank">' + val + '</a>';
                    },
                    statistics: function(key, val) {
                        var entity_map = {
                                Replies: function() {
                                    return stats_symbol_builder('Replies') || '&#128172;';
                                },
                                Plus_Ones: function() {
                                    return stats_symbol_builder('Plus_Ones') || '+';
                                },
                                Reshares: function() {
                                    return stats_symbol_builder('Reshares') || '&#8631;';
                                }
                            },
                            html = '<b>Statistics:</b>&emsp;',
                            v;

                        for(v in val) {
                            html += entity_map[v]() + '&nbsp;' + val[v] + '&emsp;';
                        }

                        html = html.trim();

                        return html;
                    }
                };
            if (ARG) {
                inner += '<ol>';
                
                for (i = 0; i < len; i++) {
                    obj = ARG[i];
                    list = '<li><ul>';
                    for (j in ARG[i]) {
                        if (j !== 'Link' && j !== 'Statistics') {
                            key = 'html';
                        } else {
                            key = j.toLowerCase();
                        }
                        list += '<li><p>' + html_iterator[key](j, ARG[i][j]) + '</p></li>';
                    }
                    list += '</ul></li>';
                    inner+= list;
                }
                
                inner += '</ol>';
            } else {
                inner = '<p>API Error</p>';
            }            

            var append = '<div id="gp_container" style="width:' + maxWidth + 'px;height:' + maxWidth + 'px;background:' + settings.background + '">' + inner + '</div>';
            append += '<div id="arrow"></div>';

            $("body").click(function(e) {
                if (e.target.id !== 'gp_container') {
                    removeGplustip(1);
                }
            }).append(append);

            setTimeout(function() {
                var container = $("#gp_container"),
                    arrow = $("#arrow"),
                    ow = container.outerWidth(),
                    oh = container.outerHeight(),
                    half_ow = .5 * ow,
                    half_oh = .5 * oh,
                    arrow_width = .02 * ow,
                    arrow_base_border = arrow_width + "px solid " + settings.background,
                    tip_translate = 3 * arrow_width,
                    applyArrowCSS = function(orientation) {
                        switch (orientation) {
                            case 1:
                                arrow.css({
                                    "border-left": "none",
                                    "border-right": arrow_base_border
                                });
                                break;
                            case 2:
                                arrow.css({
                                    "border-right": "none",
                                    "border-left": arrow_base_border
                                });
                                break;
                            case 3:
                                arrow.css({
                                    "border-top": "none",
                                    "border-bottom": arrow_base_border
                                });
                                break;
                            case 4:
                                arrow.css({
                                    "border-bottom": "none",
                                    "border-top": arrow_base_border
                                });
                                break;
                        }
                        
                        var offset_coordinates = {
                            x: Math.cos(orientation * Math.PI) * settings.arrowOffset,
                            y: Math.sin(orientation * Math.PI) * settings.arrowOffset
                        };

                        return offset_coordinates;
                    },
                    offset_coordinates = {
                        x: 0,
                        y: 0
                    },
                    container_coordinates = {};

                arrow.css({
                    "border": arrow_width + "px solid transparent"
                });

                if (arg.event.pageX <= (.5 * ww)) {
                    if (arg.event.pageY <= (.5 * wh)) { //Q2
                        if ((arg.event.pageY - half_oh) > 0) {
                            offset_coordinates = applyArrowCSS(1);
                            container_coordinates = {
                                x: arg.event.pageX + arrow_width,
                                y: arg.event.pageY - half_oh
                            };
                        } else {
                            if ((arg.event.pageX - half_ow) > 0) {
                                offset_coordinates = applyArrowCSS(3);
                                container_coordinates = {
                                    x: arg.event.pageX + arrow_width - half_ow,
                                    y: arg.event.pageY + arrow_width
                                };
                            } else {
                                if (arg.event.pageY >= arg.event.pageX) {
                                    offset_coordinates = applyArrowCSS(1);
                                    container_coordinates = {
                                        x: arg.event.pageX + arrow_width,
                                        y: arg.event.pageY - arrow_width
                                    };
                                } else {
                                    offset_coordinates = applyArrowCSS(3);
                                    container_coordinates = {
                                        x: arg.event.pageX - arrow_width,
                                        y: arg.event.pageY + arrow_width
                                    };
                                }
                            }
                        }
                    } else { //Q3
                        if ((arg.event.pageY + half_oh + arrow_width) < wh) {
                            offset_coordinates = applyArrowCSS(1);
                            container_coordinates = {
                                x: arg.event.pageX + arrow_width,
                                y: arg.event.pageY - half_oh
                            };
                        } else {
                            if ((arg.event.pageX - half_ow) > 0) {
                                offset_coordinates = applyArrowCSS(4);
                                container_coordinates = {
                                    x: arg.event.pageX + arrow_width - half_ow,
                                    y: arg.event.pageY - oh
                                };
                            } else {
                                if ((wh - arg.event.pageY) >= arg.event.pageX) {
                                    offset_coordinates = applyArrowCSS(1);
                                    container_coordinates = {
                                        x: arg.event.pageX + arrow_width,
                                        y: arg.event.pageY + tip_translate - oh
                                    };
                                } else {
                                    offset_coordinates = applyArrowCSS(4);
                                    container_coordinates = {
                                        x: arg.event.pageX - arrow_width,
                                        y: arg.event.pageY - oh
                                    };
                                }
                            }
                        }
                    }
                } else {
                    if (arg.event.pageY <= (.5 * wh)) { //Q1
                        if ((arg.event.pageY - half_oh) > 0) {
                            offset_coordinates = applyArrowCSS(2);
                            container_coordinates = {
                                x: arg.event.pageX - ow,
                                y: arg.event.pageY - half_oh
                            };
                        } else {
                            if ((arg.event.pageX + half_ow) < ww) {
                                offset_coordinates = applyArrowCSS(3);
                                container_coordinates = {
                                    x: arg.event.pageX + arrow_width - half_ow,
                                    y: arg.event.pageY + arrow_width
                                };
                            } else {
                                if (arg.event.pageY >= (ww - arg.event.pageX)) {
                                    offset_coordinates = applyArrowCSS(2);
                                    container_coordinates = {
                                        x: arg.event.pageX - ow,
                                        y: arg.event.pageY - arrow_width
                                    };
                                } else {
                                    offset_coordinates = applyArrowCSS(3);
                                    container_coordinates = {
                                        x: arg.event.pageX + tip_translate - ow,
                                        y: arg.event.pageY + arrow_width
                                    };
                                }
                            }
                        }
                    } else { //Q4
                        if ((arg.event.pageY + half_oh + arrow_width) < wh) {
                            offset_coordinates = applyArrowCSS(2);
                            container_coordinates = {
                                x: arg.event.pageX - ow,
                                y: arg.event.pageY - half_oh
                            };
                        } else {
                            if ((arg.event.pageX + half_ow) < ww) {
                                offset_coordinates = applyArrowCSS(4);
                                container_coordinates = {
                                    x: arg.event.pageX + arrow_width - half_ow,
                                    y: arg.event.pageY - oh
                                };
                            } else {
                                if ((wh - arg.event.pageY) >= (ww - arg.event.pageX)) {
                                    offset_coordinates = applyArrowCSS(2);
                                    container_coordinates = {
                                        x: arg.event.pageX - ow,
                                        y: arg.event.pageY + tip_translate - oh
                                    };
                                } else {
                                    offset_coordinates = applyArrowCSS(4);
                                    container_coordinates = {
                                        x: arg.event.pageX + tip_translate - ow,
                                        y: arg.event.pageY - oh
                                    };
                                }
                            }
                        }
                    }
                }

                if (typeof(settings.destroyOnMouseleave) === 'boolean' && settings.destroyOnMouseleave) {
                    $("#gp_container").mouseleave(function() {
                        removeGplustip(1);
                    });
                }

                container.css({
                    "left": (container_coordinates.x + offset_coordinates.x) + "px",
                    "top": (container_coordinates.y + offset_coordinates.y) + "px",
                    "visibility": "visible"
                });
                arrow.css({
                    "left": (arg.event.pageX + offset_coordinates.x) + "px",
                    "top": (arg.event.pageY + offset_coordinates.y) + "px",
                    "visibility": "visible"
                });
                if(typeof(settings.createCallback) === 'function' && settings.createCallback) {
                    settings.createCallback();
                }
            }, settings.delay);
        }

        var animation_in_progress;
        function removeGplustip(user_destroy) {
            var handleDestroyCallback = function() {
                $("#arrow,#gp_container").remove();
                animation_in_progress = false;
                if (settings.destroyCallback) {
                    settings.destroyCallback();
                }
            };
            if (user_destroy) {
                if (settings.animationOnDestroy) {
                    if (!animation_in_progress) {
                        animation_in_progress = true;
                        $("#arrow").hide();
                        if (isSupportedAnimation(settings.animationOnDestroy)) {
                            $("#gp_container")[settings.animationOnDestroy](500, function() {
                                handleDestroyCallback();
                            });
                        } else {
                            handleDestroyCallback();
                        }
                    }
                } else {
                    handleDestroyCallback();
                }
            } else {
                handleDestroyCallback();
            }
        }
    };
})(jQuery);
