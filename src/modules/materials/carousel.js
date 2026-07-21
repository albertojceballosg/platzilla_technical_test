jQuery(document).ready(function () {
    var itemsMainDiv = ('.MultiCarousel');
    var itemsDiv = ('.MultiCarousel-inner');
    var backgroundWidth  = 148;
    var backgroundHeight = 188;
    var itemWidth        = 0;
    var itemHeight       = 0;

    jQuery ('.leftLst, .rightLst').click (function () {
        var condition = jQuery (this).hasClass ("leftLst");
        if (condition)
            click (0, this);
        else
            click (1, this)
    });

    ResCarouselSize();

    jQuery (window).resize (function () {
        ResCarouselSize ();
    });

    //this function define the size of the items
    function ResCarouselSize () {
        var incno       = 0,
            dataItems   = ("data-items"),
            itemClass   = ('.item'),
            id          = 0,
            btnParentSb = '',
            itemsSplit  = '',
            sampwidth   = jQuery(itemsMainDiv).width(),
            bodyWidth   = jQuery('body').width();
        jQuery(itemsDiv).each(function () {
            id = id + 1;
            var itemNumbers = jQuery (this).find (itemClass).length;
            sampwidth   = sampwidth * parseInt (jQuery (this).parent ().attr ('data-multiplier'));
            btnParentSb = jQuery (this).parent().attr (dataItems);
            itemsSplit  = btnParentSb.split (',');
            jQuery(this).parent().attr("id", "MultiCarousel" + id);

            if (bodyWidth >= 1200) {
                incno = itemsSplit[3];
                itemWidth = sampwidth / incno;

                console.log(itemWidth)
            }
            else if (bodyWidth >= 992) {
                incno = itemsSplit[2];
                itemWidth = sampwidth / incno;
            }
            else if (bodyWidth >= 768) {
                incno = itemsSplit[1];
                itemWidth = sampwidth / incno;
            }
            else {
                incno = itemsSplit[0];
                itemWidth = sampwidth / incno;
            }
            console.log('item' + incno);
            jQuery(this).css({ 'transform': 'translateX(0px)', 'width': (itemWidth * itemNumbers) + 50 });
            jQuery(this).find(itemClass).each(function () {
                jQuery(this).outerWidth(itemWidth);
                itemHeight  = (((itemWidth * 100)/backgroundWidth) * backgroundHeight)/100;
                jQuery(this).outerHeight (itemHeight);
            });
            if (itemNumbers < incno) {
                jQuery(".leftLst").addClass("hide");
                jQuery(".rightLst").addClass("hide");
            } else {

            }
            jQuery (".leftLst").addClass ("over");
            jQuery (".rightLst").removeClass ("over");

        });
    }

    //this function used to move the items
    function ResCarousel(e, el, s) {
        var leftBtn       = ('.leftLst');
        var rightBtn      = ('.rightLst');
        var translateXval = '';
        var divStyle      = jQuery(el + ' ' + itemsDiv).css('transform');
        var values        = divStyle.match(/-?[\d\.]+/g);
        var xds = Math.abs(values[4]);
        if (e == 0) {
            translateXval = parseInt(xds) - parseInt(itemWidth * s);
            jQuery(el + ' ' + rightBtn).removeClass("over");

            if (translateXval <= itemWidth / 2) {
                translateXval = 0;
                jQuery(el + ' ' + leftBtn).addClass("over");
            }
        }
        else if (e == 1) {
            var itemsCondition = jQuery(el).find(itemsDiv).width() - jQuery(el).width();
            translateXval = parseInt(xds) + parseInt(itemWidth * s);
            jQuery(el + ' ' + leftBtn).removeClass("over");

            if (translateXval >= itemsCondition - itemWidth / 2) {
                translateXval = itemsCondition;
                jQuery(el + ' ' + rightBtn).addClass("over");
            }
        }
        jQuery(el + ' ' + itemsDiv).css('transform', 'translateX(' + -translateXval + 'px)');
    }

    //It is used to get some elements from btn
    function click(ell, ee) {
        var Parent = "#" + jQuery(ee).parent().attr("id");
        var slide = jQuery(Parent).attr("data-slide");
        ResCarousel(ell, Parent, slide);
    }

});

    jQuery (function(){
        var treeView = jQuery("#treeview").dxTreeView ({
        items: documents,
        //width: 800,
        searchEnabled: true
    }).dxTreeView ("instance");

    jQuery ("#searchMode").dxSelectBox ({
        items: ["contains", "startswith"],
        value: "contains",
        onValueChanged: function(data) {
            treeView.option("searchMode", data.value);
        }
    });
});