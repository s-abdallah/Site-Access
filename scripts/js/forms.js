function smtForms() {
    var settings, formName, formObject, buttonClicked = null;
    var valid = true;
    this.init = function(options) {
        settings = $.extend({
            submitButton: {
                defaultCopy: "",
                waitingCopy: "Please Wait..."
            },
            messages: {
                "default": "* indicates a required field",
                success: "Your message has been sent",
                failure: "Correct the highlighted fields"
            }
        }, options);
        $(document).on("click", "form#filter input[data-type='filter_cancel']", function(e) {
            $("#loaderpanel").show();
            window.location.replace("media.php?action=" + $("form#filter input[id=action]").val() + ($("form#filter input[id=rid]").length && $("form#filter input[id=rid]").val() != "" ? "&rid=" + $("form#filter input[id=rid]").val() : "") + ($("form#filter input[id=mid]").length && $("form#filter input[id=mid]").val() != "" ? "&mid=" + $("form#filter input[id=mid]").val() : "") + ($("form#filter input[id=field]").length && $("form#filter input[id=field]").val() != "" ? "&field=" + $("form#filter input[id=field]").val() : ""));
        });
        $(document).on("click", "form#media_batchupload input[data-type='batch_submit']", function(e) {
            if ($("form#media_batchupload input[name=filetoupload]").val() != "") {
                if ($(this).attr("data-confirm")) {
                    var r = confirm($(this).attr("data-confirm"));
                } else {
                    var r = confirm("Are you sure you want to batch upload this zip file?");
                }
                if (r == true) {
                    $("#loaderpanel").show();
                    return true;
                } else {
                    alert("Batch upload canceled.");
                }
            } else {
                alert("Invalid batch file selected.");
                return false;
            }
        });
        $(document).on("click", "form#media_snapshots input[data-type='snapshot_submit']", function(e) {
            $("#loaderpanel").show();
        });
        $(document).on("click", ".install_snapshot", function(e) {
            var r = confirm("Are you sure you want to install this snapshot?");
            if (r == true) {
                $("#loaderpanel").show();
                $.post("snapshots.php", {
                    name: $(this).attr("data-name"),
                    path: $(this).attr("data-path"),
                    install: "true"
                }, function(result) {
                    $("#loaderpanel").hide();
                    if (result == 1) {
                        alert("Snapshot has been installed successfully.");
                    } else {
                        alert("Install snapshot canceled.");
                    }
                });
                return false;
            }
        });
        $(document).on("click", ".delete_snapshot", function(e) {
            var r = confirm("Are you sure you want to delete this snapshot?");
            if (r == true) {
                $("#loaderpanel").show();
                $.post("snapshots.php", {
                    name: $(this).attr("data-name"),
                    path: $(this).attr("data-path"),
                    "delete": "true"
                }, function(result) {
                    $("#loaderpanel").hide();
                    if (result == 1) {
                        alert("Snapshot has been removed successfully.");
                        location.reload();
                    } else {
                        alert("Snapshot canceled.");
                    }
                });
                return false;
            }
        });
        $(document).on("click", ".neoclone", function(e) {
            var duplicate = $("fieldset.clone").length;
            var dlimit = 5;
            if (duplicate < dlimit) {
                var $this = $(this), $cloneElement = $this.closest(".clone"), $clone = $cloneElement.clone();
                $cloneElement.before($clone);
                $clone.fadeIn(1e3, "linear", function() {
                    $(this).removeClass("hide");
                });
                $this.removeClass("disable");
                $this.html($this.data("text")).addClass($this.data("toggle-class")).removeClass("neoclone");
                duplicate += 1;
            } else if (duplicate == dlimit) {
                $(this).addClass("disable");
            }
            return false;
        });
        $(document).on("click", ".neoDclone", function(e) {
            var $this = $(this);
            $this.closest(".clone").fadeOut(100, "linear", function() {
                $(this).remove();
                $(".neoclone").removeClass("disable");
            });
            return false;
        });
        $(document).on("click", ".neoGclone", function(e) {
            var gduplicate = $("div.gclone").length;
            var gdLimit = 3;
            if (gduplicate < gdLimit) {
                var $this = $(this), $cloneElement = $this.closest(".gclone"), $clone = $cloneElement.clone();
                $cloneElement.before($clone);
                $clone.fadeIn(1e3, "linear", function() {
                    $(this).removeClass("hide");
                });
                $this.removeClass("disable");
                $this.html($this.data("text")).addClass($this.data("toggle-class")).removeClass("neoGclone");
                gduplicate += 1;
            } else if (gduplicate == gdLimit) {
                $(this).addClass("disable");
            }
            return false;
        });
        $(document).on("click", ".neoDGclone", function(e) {
            var $this = $(this);
            $this.closest(".gclone").fadeOut(100, "linear", function() {
                $(this).remove();
                $(".neoGclone").removeClass("disable");
            });
            return false;
        });
        $(document).on("click", "form#filter input[data-type='filter_submit']", function(e) {
            $("#loaderpanel").show();
        });
        $(document).on("change", "form select.neopagi", function(e) {
            var url = $(this).val();
            if (url) {
                window.location = url;
            }
            return false;
        });
        $(document).on("click", "form input[data-type=tool_submit]", function(e) {
            settings.submitButton.defaultCopy = $(this).val();
            buttonClicked = $(this);
            formObject = buttonClicked.parent().parent();
            formName = buttonClicked.parent().parent().attr("name");
            if ($(this).val() != "Remove") {
                smtForms.submit(e);
            } else {
                e.preventDefault();
                var warning = prompt("WARNING: Removing a record cannot be undone!\n\nPlease enter 'YES' in the box below to continue or cancel to exit.\n\n", "");
                if (warning === "YES") {
                    var url = window.location.href + "&remove=true";
                    window.location.replace(url);
                }
            }
        });
        $(document).on("click", ".js-showpassword", function(e) {
            var pWord = $("form input[data-type=password]");
            if (pWord.attr("type") == "text") {
                $("form input[data-type=password]").attr("type", "password");
                $(this).html("Show Password");
            } else {
                $("form input[data-type=password]").attr("type", "text");
                $(this).html("Hide Password");
            }
        });
        function updateQuery() {
            if ($("select[id=query_orderbyprimary]").val() != "" && $("select[id=query_dirprimary]").val() != "") {
                var returnFields = '"*"';
                if (!$("form[id=query] input[id=chk_group_everything]").prop("checked")) {
                    returnFields = "[";
                    $("form[id=query] input[type=checkbox]").each(function(i) {
                        if ($(this).prop("checked")) {
                            returnFields += (returnFields != "[" ? "," : "") + '"' + $("select[id=query_tool]").val() + "_" + $(this).val() + '"';
                        }
                    });
                    returnFields += "]";
                }
                var orderBy = "[";
                orderBy += '"' + $("select[id=query_tool]").val() + "_" + $("select[id=query_orderbyprimary]").val() + '"';
                if ($("select[id=query_orderbysecondary]").val() != "" && $("select[id=query_dirsecondary]").val() != "") {
                    orderBy += ',"' + $("select[id=query_tool]").val() + "_" + $("select[id=query_orderbysecondary]").val() + '"';
                }
                orderBy += "]";
                var orderByDir = "[";
                orderByDir += '"' + $("select[id=query_dirprimary]").val() + '"';
                if ($("select[id=query_dirsecondary]").val() != "" && $("select[id=query_dirsecondary]").val() != "") {
                    orderByDir += ',"' + $("select[id=query_dirsecondary]").val() + '"';
                }
                orderByDir += "]";
                $("textarea[id=query_code]").val('$data = $db->DB_CONTENT_GET("' + $("select[id=query_tool]").val() + '",' + returnFields + "," + orderBy + "," + orderByDir + "," + $("select[id=query_media]").val() + ");");
            }
        }
        function buildOrderBy(a) {
            var data = "";
            if (a === "all") {
                $("form[id=query] input[type=checkbox]:not([id=chk_group_everything])").each(function(i) {
                    data += '<option value="' + $(this).val() + '">' + $(this).val().replace("_", " ") + "</option>";
                });
            } else {
                $("form[id=query] input[type=checkbox]").each(function(i) {
                    if ($(this).prop("checked")) {
                        data += '<option value="' + $(this).val() + '">' + $(this).val().replace("_", " ") + "</option>";
                    }
                });
            }
            $("select[id=query_orderbyprimary]").html('<option value="">----------</option>' + data);
        }
        $(document).on("change", "form[id=query] select[id=query_tool]", function(e) {
            $("#loaderpanel").show();
            var hashes = url.slice(url.indexOf("?") + 1).split("&");
            var remove = "";
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split("=");
                if (hash[0] == "tool") {
                    remove = "?tool=" + hash[1];
                }
            }
            url = url.replace(remove, "");
            if ($(this).val() != "" && $(this).val() != "----------") {
                window.location.replace(url + "?tool=" + $(this).val());
            } else {
                alert("Oops...it looks like you made an invalid selection.");
                window.location.replace(url);
            }
        });
        $(document).on("change", "form[id=query] input[type=checkbox]", function(e) {
            var cVal = cVal = $("textarea[id=query_code]").val();
            if ($(this).val() !== "*") {
                $("form[id=query] input[id=chk_group_everything]").prop("checked", false);
                buildOrderBy("pick");
            } else {
                $("form[id=query] input[type=checkbox]").prop("checked", false);
                $("form[id=query] input[id=chk_group_everything]").prop("checked", true);
                buildOrderBy("all");
            }
            updateQuery();
        });
        $(document).on("change", "form[id=query] select[id=query_media]", function(e) {
            updateQuery();
        });
        $(document).on("focusout click", ".neo__forms fieldset input", function(e) {
            var text_val = $(this).val();
            if (text_val === "") {
                $(this).removeClass("has-value");
            } else {
                $(this).addClass("has-value");
            }
        });
        $(document).on("change", ".neo__forms input[type=file]", function(e) {
            var input = $(this)[0];
            var img = $(".js--image-preview");
            if (input.files && input.files[0]) {
                if (input.files[0].type.match("image.*")) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        img.addClass("js--no-default");
                        img.css("background-image", "url(" + e.target.result + ")");
                        img.css("background-size", "size");
                    };
                    reader.readAsDataURL(input.files[0]);
                } else if (input.files[0].type.match("pdf.*")) {
                    img.addClass("js--no-default");
                    img.css("background-image", "url(ui/pdficon.jpg)");
                    img.css("background-size", "contain");
                } else if (input.files[0].type.match("zip.*")) {
                    img.addClass("js--no-default");
                    img.css("background-image", "url(ui/zipicon.jpg)");
                    img.css("background-size", "contain");
                } else {
                    alert("The file does not match existing type!");
                }
            }
        });
        $(document).on("change", "#filetoupload", function(e) {});
        $(document).on("drop", "#file-drag", function(e) {
            e.preventDefault();
            e.stopPropagation();
            $("#filetoupload").prop("files", e.originalEvent.dataTransfer.files);
        });
        $(document).on("dragover", "#file-drag", function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
        $(document).on("dragenter", "#file-drag", function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
        $(window).load(function() {
            $("fieldset.check-value").each(function() {
                inp = $(this).find("input");
                val = inp.val();
                if (val && typeof val !== "undefined") {
                    $(this).addClass("has-value");
                }
                id = inp.attr("id");
                if (id == "tags") {
                    $(this).addClass("has-value");
                }
            });
            $("#tags").tagsInput({
                width: "auto",
                height: "auto"
            });
            $("#neotags").tagsInput({
                width: "auto",
                height: "52px",
                onChange: function(elem, elem_tags) {
                    var keyword = $("#filter_keyword").val();
                    if (typeof elem_tags !== "undefined" && keyword.indexOf(elem_tags) == -1) {
                        if (keyword) $("#filter_keyword").val(keyword + "," + elem_tags); else $("#filter_keyword").val(elem_tags);
                    }
                },
                onRemoveTag: function(elem) {
                    var keyword = $("#filter_keyword").val();
                    keyword = keyword.replace(elem, "");
                    keyword = keyword.replace(",,", ",");
                    keyword = keyword.replace(/^,|,$/g, "");
                    $("#filter_keyword").val(keyword);
                }
            });
        });
        $(document).on("change", "form select.neomultis", function(e) {
            var id = $(this).attr("id");
            var selectedOptions = [];
            $("select[name='" + id + "[]'] option:selected").each(function() {
                selectedOptions.push($(this).text());
            });
            var result = "";
            $.each(selectedOptions, function(i, val) {
                result += "<li>" + val + "</li>";
            });
            $("ul." + id).html(result);
            return false;
        });
        $(document).on("change", "form[id=query] select[id=query_dirprimary]", function(e) {
            if ($(this).val() != "" && $(this).val() != "----------") {
                updateQuery();
            } else {
                alert("Oops...it looks like you made an invalid selection.");
            }
        });
        $(document).on("change", "form[id=query] select[id=query_dirsecondary]", function(e) {
            updateQuery();
        });
        $(document).on("change", "form[id=query] select[id=query_orderbyprimary]", function(e) {
            if ($(this).val() != "" && $(this).val() != "----------") {
                updateQuery();
            } else {
                alert("Oops...it looks like you made an invalid selection.");
            }
        });
        $(document).on("change", "form[id=query] select[id=query_orderbysecondary]", function(e) {
            updateQuery();
        });
        $(document).on("change", "form select[id=selecttoedit]", function(e) {
            $("#loaderpanel").show();
            var url = window.location.href.replace("&completed=true", "");
            var hashes = url.slice(url.indexOf("?") + 1).split("&");
            var remove = "";
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split("=");
                if (hash[0] == "rid") {
                    remove = "&rid=" + hash[1];
                }
            }
            url = url.replace(remove, "");
            if ($(this).val() != "" && $(this).val() != "----------") {
                window.location.replace(url + "&rid=" + $(this).val());
            } else {
                alert("Oops...it looks like you made an invalid selection.");
                window.location.replace(url);
            }
        });
    };
    this.validate = function(a) {
        switch (a.attr("data-type")) {
          case "name":
            regex = /^(([A-Za-z]+[\-\']?)*([A-Za-z]+)?\s)+([A-Za-z]+[\-\']?)*([A-Za-z]+)?$/i;
            break;

          case "date":
            regex = /(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.](19|20)\d\d/;
            break;

          case "title":
            regex = /^[A-Za-z0-9\.]/i;
            break;

          case "email":
            regex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
            break;

          case "filepath":
            regex = /^[-\w^&'@{}[\],$=!#().%+~ ]+$/i;
            break;

          case "cfilename":
            regex = /^[a-z0-9_-]+$/i;
            break;

          case "password":
            regex = /^[a-z0-9_-]+$/i;
            break;

          case "orderweight":
            regex = /^[0-9]+$/i;
            break;

          default:
            regex = /^(.*?)/;
            break;
        }
        if (a.attr("data-id") === "selectmedia") {
            $.post("media.php", {
                mapcount: "true",
                id: a.attr("id"),
                rid: a.attr("data-rid")
            }, function(result) {
                console.log(result);
                if (result == "" || result == 0 || result == "0") {
                    a.parent().addClass("error");
                    valid = false;
                }
            });
        } else {
            var checkVal;
            if (a.hasClass("richeditor")) {
                checkVal = tinyMCE.get(a.attr("id")).getContent();
            } else if (a.attr("id") === "mapmarkers_position_lat" || a.attr("id") === "mapmarkers_position_lon") {
                a = $(".maparea");
                checkVal = $("#mapmarkers_position_lat").val();
            } else {
                checkVal = a.val();
            }
            if (!regex.test(checkVal) || checkVal == "") {
                a.parent().addClass("error");
                a.val("");
                valid = false;
                return false;
            } else {
                return true;
            }
        }
    };
    this.toggleFieldHighlight = function(a) {
        a.toggleClass("active");
    };
    this.submit = function(a) {
        a.preventDefault();
        valid = true;
        $("#loaderpanel").show();
        buttonClicked.prop("disabled", true).val(settings.submitButton.waitingCopy).attr("title", settings.submitButton.waitingCopy);
        var errorMessage = "";
        smtForms.clearAlert(settings.messages.default);
        $("form[name=" + formName + "] > fieldset").removeClass("error");
        if (/media.php/g.test($("form[name=" + formName + "]").attr("action"))) {
            var editMode = false;
            if ($("form[name=" + formName + "] input[name=filetoupload]").attr("disabled") === "disabled") {
                editMode = true;
            }
            if (!smtForms.validate($("form[name=" + formName + "] input[name=caption]"))) {
                valid = false;
                errorMessage += (errorMessage == "" ? "" : ", ") + "invalid caption";
            }
            if (!editMode) {
                if ($("form[name=" + formName + "] input[name=link]").val()) {
                    if (!smtForms.validate($("form[name=" + formName + "] input[name=link]"))) {
                        valid = false;
                        (errorMessage == "" ? "" : ", ") + "invalid link";
                    }
                }
                if ($("form[name=" + formName + "] input[name=filetoupload]").val()) {
                    regex = /^[a-z0-9\.:\\_-]+$/i;
                    str = $("form[name=" + formName + "] input[name=filetoupload]").val();
                    dotCount = (str.match(/\./g) || []).length;
                    if (!regex.test(str) || dotCount != 1) {
                        valid = false;
                        errorMessage += (errorMessage == "" ? "" : ", ") + "invalid file upload";
                        $("form[name=" + formName + "] input[name=filetoupload]").parent().addClass("error");
                        $("form[name=" + formName + "] input[name=filetoupload]").val("");
                    } else {
                        fileSize = parseInt($("#filetoupload")[0].files[0].size / 1024 / 1024);
                        maxSize = parseInt($("form[name=" + formName + "] input[name=maxfilesize]").val());
                        if (fileSize > maxSize) {
                            valid = false;
                            errorMessage += (errorMessage == "" ? "" : ", ") + "file too large";
                            $("form[name=" + formName + "] input[name=filetoupload]").parent().addClass("error");
                            $("form[name=" + formName + "] input[name=filetoupload]").val("");
                        }
                        if (!smtForms.validate($("form[name=" + formName + "] input[name=filename]"))) {
                            valid = false;
                            errorMessage += (errorMessage == "" ? "" : ", ") + "invalid custom filename";
                            $("form[name=" + formName + "] input[name=filename]").parent().addClass("error");
                            $("form[name=" + formName + "] input[name=filename]").val("");
                        }
                        var str = $("form[name=" + formName + "] input[name=filetoupload]").val();
                        checkMe = $("form[name=" + formName + "] input[name=filetypes]").val().split(",");
                        var maatch = false;
                        for (x in checkMe) {
                            if (str.indexOf(checkMe[x]) > 0) {
                                maatch = true;
                                break;
                            }
                        }
                        if (!maatch) {
                            valid = false;
                            errorMessage += (errorMessage == "" ? "" : ", ") + "invalid file type";
                            $("form[name=" + formName + "] input[name=filetoupload]").parent().addClass("error");
                            $("form[name=" + formName + "] input[name=filetoupload]").val("");
                        }
                    }
                }
                if (!$("form[name=" + formName + "] input[name=filetoupload]").val() && !$("form[name=" + formName + "] input[name=link]").val()) {
                    valid = false;
                    errorMessage += (errorMessage == "" ? "" : ", ") + "requires at least one file OR link";
                    $("form[name=" + formName + "] input[name=filetoupload]").parent().addClass("error");
                    $("form[name=" + formName + "] input[name=filetoupload]").val("");
                    $("form[name=" + formName + "] input[name=link]").parent().addClass("error");
                    $("form[name=" + formName + "] input[name=link]").val("");
                }
            } else {
                if (!$("form[name=" + formName + "] input[name=link]").attr("disabled") && !smtForms.validate($("form[name=" + formName + "] input[name=link]"))) {
                    valid = false;
                    (errorMessage == "" ? "" : ", ") + "invalid link";
                }
            }
            smtForms.validationDone(valid, errorMessage);
        } else {
            var rqFieldCnt = $("form[name=" + formName + "]").find("input[required],select[required],textarea[required],iframe[required]").length;
            $("form[name=" + formName + "]").find("input[required],select[required],textarea[required],iframe[required]").each(function(i, v) {
                smtForms.validate($(this));
                if (i + 1 == rqFieldCnt) {
                    setTimeout(function() {
                        smtForms.validationDone(valid, errorMessage);
                    }, 1e3);
                }
            });
        }
    };
    this.validationDone = function(a, b) {
        if (!a) {
            $("#loaderpanel").hide();
            smtForms.alertUser(0, b != "" ? b : settings.messages.failure);
        } else if (a) {
            $(formObject).submit();
        }
    };
    this.alertUser = function(a, b) {
        $("form[name=" + formName + "] label[id=messages]").html(a == 1 ? "SUCCESS: " + settings.messages.success : "ERROR: " + b).parent().addClass(a == 1 ? "success" : "error");
        buttonClicked.prop("disabled", false).val(settings.submitButton.defaultCopy).attr("title", settings.submitButton.defaultCopy);
    };
    this.sendMessage = function(a, b, c) {
        $.post("_scripts/php/sendemail.php", {
            message: a,
            sendtoemail: b && b != "" ? b : "",
            sendfromname: c
        }, function(result) {
            if (result === "SUCCESS") {
                smtForms.alertUser(1);
                $("form[name=" + formName + "]")[0].reset();
                setTimeout(function() {
                    smtForms.clearAlert(settings.messages.default);
                }, 3e3);
            } else {
                smtForms.alertUser(0, "An unknown error has occurred");
            }
        });
    };
    this.clearAlert = function(a) {
        $("form[name=" + formName + "] > label").empty().html(a).removeClass("error").removeClass("success");
    };
}

smtForms = new smtForms();

smtForms.init();