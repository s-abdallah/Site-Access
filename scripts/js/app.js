var mapCenter = {
  lat: 45.2555351,
  lng: -111.3189939,
};

var removalCnt = 0;

var removeMe = Array();

var removalTimer = "";

var url = window.location.href;

var selectFull = false;

$(document).ready(function () {
  interactionTimeout = setTimeout(function () {
    expireSession();
  }, 18e5);
  function expireSession() {
    window.location.href = "login.php?logout";
  }
  $(window).scroll(function () {
    if (
      $(window).scrollTop() >= 200 &&
      $(".js-backtotop").css("display") != "block"
    ) {
      $(".js-backtotop").fadeIn(250);
    } else if (
      $(window).scrollTop() < 200 &&
      $(".js-backtotop").css("display") == "block"
    ) {
      $(".js-backtotop").fadeOut(250);
    }
  });
  $(document).on("click", ".js-backtotop div", function (e) {
    if ($(this).attr("rel") === "top") {
      $("html,body").animate(
        {
          scrollTop: 0,
        },
        500
      );
    } else {
      $("html,body").animate(
        {
          scrollTop: $(document).height(),
        },
        500
      );
    }
  });
  $(".allowreorder").sortable({
    stop: function (event, ui) {
      var fieldName = $("li.dragtosetorder")
        .eq(0)
        .find("div")
        .attr("data-field");
      var rid = $("li.dragtosetorder").eq(0).find("div").attr("data-rid");
      var newOrder = Array();
      $("li.dragtosetorder").each(function () {
        newOrder.push($(this).find("div").attr("id"));
      });
      $.post(
        "media.php",
        {
          reorder: "true",
          rid: rid,
          field: fieldName,
          order: newOrder,
        },
        function (result) {
          console.log(result);
        }
      );
    },
  });
  $(".js-sortable")
    .sortable({
      placeholder: "ui-sortable-placeholder",
      axis: "y",
      containment: "parent",
      update: function (event, ui) {
        var data = $(this).sortable("toArray", {
          attribute: "data-order",
        });
        $.ajax({
          data: {
            data: data,
            page: $(this).data("page"),
            user: $(this).data("user"),
          },
          type: "POST",
          url: "scripts/php/sortable.php",
        });
      },
      start: function (event, ui) {
        ui.item
          .css("border", "2px solid rgba(0, 0, 0, 0.75)")
          .append('<span class="ui-icon ui-icon-check icons"></span>');
      },
      stop: function (event, ui) {
        ui.item.css("border", "");
        ui.item.children(".icons").remove();
      },
    })
    .disableSelection();
  $("[class*=magnific-single]").magnificPopup({
    type: "image",
    closeOnContentClick: false,
    closeBtnInside: true,
    enableEscapeKey: false,
    closeOnBgClick: false,
    image: {
      verticalFit: true,
      titleSrc: "data-title",
    },
    callbacks: {
      open: function () {
        if (removalTimer == null) {
          this.close();
        }
      },
    },
  });
  $("[class*=magnific-iframe]").magnificPopup({
    type: "iframe",
    closeOnContentClick: false,
    closeOnBgClick: false,
    enableEscapeKey: false,
    verticalFit: true,
    callbacks: {
      open: function () {
        if (removalTimer == null) {
          this.close();
        }
      },
      beforeClose: function () {
        if ($("[data-id=selectmedia]").length) {
          $("iframe[data-id=selectmedia]").each(function (index) {
            iframeRefresh($(this));
          });
        }
      },
    },
  });
  if (url.indexOf("action=remove") == -1) {
    $("input[data-type=date]").datepicker({
      onSelect: function (date) {
        var text_val = $(this).parent().find("input").val();
        if (text_val === "") {
          $(this).removeClass("has-value");
        } else {
          $(this).addClass("has-value");
        }
      },
    });
  }
  $menu = $("header nav ul");
  $menu.find("li").hover(function (e) {
    $(this).find("ul").stop().slideToggle();
  });
  $(".nav-open").on("click", function (e) {
    e.preventDefault();
    $(this).toggleClass("nav-close");
    $("header nav").find("ul").stop().slideToggle();
    $("header nav").find("ul li ul").stop().slideToggle();
  });
  $("#leftmenu").multilevelpushmenu({
    containersToPush: [$("#pushobj")],
    collapsed: true,
    wrapperClass: "mlpm_w",
    menuInactiveClass: "mlpm_inactive",
    onItemClick: function () {
      var event = arguments[0],
        $menuLevelHolder = arguments[1],
        $item = arguments[2],
        options = arguments[3];
      var itemHref = $item.find("a:first").attr("href");
      location.href = itemHref;
    },
  });
  $(".neotable").DataTable({
    responsive: true,
  });
  function flagForRemoval(a) {
    clearTimeout(removalTimer);
    removalTimer = null;
    if (a.hasClass("removeme")) {
      a.removeClass("removeme");
      var i = removeMe.indexOf(
        a.find("div.js-flagforremoval").attr("data-removeid")
      );
      removeMe.splice(i, 1);
      if (
        removeMe.length === 0 &&
        $(".neo__media-confirm").css("display") === "block"
      ) {
        showRemovalConfirm();
      }
    } else {
      a.addClass("removeme");
      removeMe.push(a.find("div.js-flagforremoval").attr("data-removeid"));
      if ($(".neo__media-confirm").css("display") === "none") {
        showRemovalConfirm();
      }
    }
    $(".neo__media-confirm span").html(removeMe.length);
  }
  $(document).on("click", "div.js-flagforremoval", function (e) {
    flagForRemoval($(this).parent());
  });
  $(document).on("click", "div.js-addtorecord", function (e) {
    associateMedia($(this));
  });
  function associateMedia(a, b) {
    var rid = a.attr("data-rid");
    var bookmark = a.find("span");
    selectedField = a.attr("data-field");
    if (!bookmark.hasClass("selected") && selectFull === false) {
      $.post(
        "media.php",
        {
          map: "true",
          id: a.attr("id"),
          rid: a.attr("data-rid"),
          field: a.attr("data-field"),
          tool: a.attr("data-tool"),
        },
        function (result) {
          bookmark.addClass("selected");
          if (result === "full") {
            selectFull = true;
            $(window.parent.document)
              .find("a[data-field=" + a.attr("data-field") + "]")
              .hide();
          }
        }
      );
    } else if (!bookmark.hasClass("selected") && selectFull === true) {
      alert(
        "Oops...you have already selected the max number of media items for this field.  You will need to remove one or more to be able to select new media."
      );
    } else if (bookmark.hasClass("selected")) {
      $.post(
        "media.php",
        {
          map: "true",
          id: a.attr("id"),
          rid: a.attr("data-rid"),
          field: a.attr("data-field"),
          tool: a.attr("data-tool"),
        },
        function (result) {
          bookmark.removeClass("selected");
          selectFull = false;
          $(window.parent.document)
            .find("a[data-field=" + a.attr("data-field") + "]")
            .show();
          location.reload();
        }
      );
    }
  }
  function iframeRefresh(a) {
    a.attr("src", a.attr("src"));
  }
  $(document).on("mousedown", ".js-removemedia", function (e) {
    showRemovalConfirm();
  });
  function showRemovalConfirm() {
    if ($(".neo__media-confirm").css("display") === "none") {
      $("form[id=filter]")
        .find("input, textarea, button, select")
        .attr("disabled", "disabled");
      $("#loaderpanel").show();
      $(".neo__media-confirm").fadeIn(250, function () {
        $(".neo__media-list li").find(".remove").show();
        $("#loaderpanel").hide();
      });
    } else {
      $("#loaderpanel").show();
      $(".neo__media-confirm").fadeOut(250, function () {
        $("form[id=filter]")
          .find("input, textarea, button, select")
          .removeAttr("disabled");
        $(".neo__media-list li").removeClass("removeme");
        $(".neo__media-list li").find(".remove").hide();
        removalCnt = 0;
        $(".neo__media-confirm span").html(removalCnt);
        $("#loaderpanel").hide();
      });
    }
    return;
  }
  $(document).on(
    "click",
    "form[id=remove_confirm] input[id=remove_cancel]",
    function (e) {
      showRemovalConfirm();
    }
  );
  $(document).on(
    "click",
    "form[id=remove_confirm] input[id=remove_submit]",
    function (e) {
      e.preventDefault();
      var warning = prompt(
        "WARNING: Removing media cannot be undone and may affect entries in one or more tools!\n\nPlease enter 'YES' in the box below to continue or cancel to exit.\n\n",
        ""
      );
      if (warning === "YES") {
        $("#loaderpanel").show();
        $.post(
          "media.php?remove=true",
          {
            items: removeMe.toString(),
          },
          function (result) {
            console.log(result);
            window.location.replace(window.location.href);
          }
        );
      }
    }
  );
  $(document).on("mouseup", ".neo__media-list div", function (e) {
    if (removalTimer != null) {
      clearTimeout(removalTimer);
    }
  });
  $(document).on("click", "table tr:not(:first-child)", function (e) {
    var a = $(this);
    if (a.attr("data-action") === "edit") {
      $("#loaderpanel").show();
      window.location.href =
        (a.attr("data-tool") == "users" ? "users" : "tool") +
        ".php?action=" +
        a.attr("data-action") +
        (a.attr("data-tool") == "users" ? "" : "&tool=" + a.attr("data-tool")) +
        "&rid=" +
        a.attr("data-rid");
    }
  });
});

function initMap() {
  var toolName = $("form[method=post]").attr("id").split("_")[0];
  var markersArray = [];
  var lat, lon;
  var map = new google.maps.Map(document.getElementById("mapcanvas"), {
    disableDoubleClickZoom: true,
    scrollwheel: false,
    zoom: 13,
    center: mapCenter,
    styles: [
      {
        featureType: "transit",
        stylers: [
          {
            visibility: "off",
          },
        ],
      },
      {
        featureType: "poi",
        stylers: [
          {
            visibility: "off",
          },
        ],
      },
    ],
    disableDefaultUI: true,
    zoomControl: true,
  });
  if (
    $("input[id=" + toolName + "_position_lat]").val() != "" &&
    $("input[id=" + toolName + "_position_lon]").val() != ""
  ) {
    lat = $("input[id=" + toolName + "_position_lat]").val();
    lon = $("input[id=" + toolName + "_position_lon]").val();
    placeMarker(lat, lon);
  }
  google.maps.event.addListener(map, "click", function (event) {
    map.clearOverlays();
    placeMarker(event.latLng.lat(), event.latLng.lng());
  });
  function placeMarker(lat, lon) {
    $("input[id=" + toolName + "_position_lat]").val(lat);
    $("input[id=" + toolName + "_position_lon]").val(lon);
    map.panTo(new google.maps.LatLng(lat, lon));
    var marker = new google.maps.Marker({
      position: new google.maps.LatLng(lat, lon),
      map: map,
    });
    markersArray.push(marker);
  }
  google.maps.Map.prototype.clearOverlays = function () {
    for (var i = 0; i < markersArray.length; i++) {
      markersArray[i].setMap(null);
    }
    markersArray.length = 0;
  };
}
