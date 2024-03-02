<form name="media_<?=strtolower($_GET['action'])?>" id="media_<?=strtolower($_GET['action'])?>" action="<?=basename($_SERVER['PHP_SELF']).(isset($_GET['action'])?"?action=".$_GET['action']:"").(isset($_GET['rid'])?"&rid=".$_GET['rid']:"")?>" enctype="multipart/form-data" method="post" class="neo__forms">
    <input type="hidden" name="maxfilesize" id="maxfilesize" value="<?=$max_size?>" />
    <input type="hidden" name="filetypes" id="filetypes" value="<?=str_replace(" ","",$supportedFileTypes)?>" />
    <input type="hidden" name="timestamp" id="timestamp" value="<?=$timeStamp?>" />
    <div id="response" class="<?=$response[1]?>"><?=$response[0]?></div>


    <aside>


      <fieldset <?=(isset($data) && $data['filetype'] != "vr" && $data['filetype'] != "link" && $data['filetype'] != "mov"?"style=\"display: none;\"":"")?>>
          <input type="text" data-type="cfilename" name="filename" id="filename" value="<?=(isset($data)?$data['filename']:"")?>" class="<?=(isset($data)?"has-value":"")?>" <?=(isset($data)?"disabled=\"disabled\"":"")?> />
          <label for="filename">* Filename (my_custom_filename)</label>
          <p>Custom name to save file as excluding extension</p>
      </fieldset>
      <?php if((isset($data) && $data['filetype'] != "vr" && $data['filetype'] != "link" && $data['filetype'] != "mov"?"style=\"display: none;\"":"")){ ?>
        <fieldset class="has-value">
          <?=$data['filepath']?>
          <label>* Filename</label>
          <p>Custom name to save file as excluding extension</p>
        </fieldset>
      <?php } ?>

      <fieldset>
          <input type="text" data-type="link" name="link" id="link" value="<?=(isset($data) && $data != ""?$data['link']:"")?>" class="<?=(isset($data) && $data != ""?"has-value":"")?>"
          <?=(isset($data) && ($data['filetype'] != "link" && $data['filetype'] != "vr" && $data['filetype'] != "mov")?"disabled=\"disabled\"":"")?> />
          <label><?php if(!isset($_GET['rid'])){ ?>AND/OR<?php } ?> Link (http://thisisthesite.com/resource)</label>
          <p>Direct link to an external resource including extension, if no file selected above</p>
      </fieldset>

      <fieldset>
          <input type="text" data-type="title" name="caption" id="caption" value="<?=(isset($data) && $data != ""?$data['caption']:"")?>" class="<?=(isset($data) && $data != ""?"has-value":"")?>" />
          <label>* Caption</label>
          <p>A short description of this item, may appear on front-end</p>
      </fieldset>

      <fieldset>
          <input type="text" data-type="title" name="subcaption" id="subcaption" value="<?=(isset($data['subcaption']) && $data['subcaption'] != ""?$data['subcaption']:"")?>" class="<?=(isset($data['subcaption']) && $data['subcaption'] != ""?"has-value":"")?>" />
          <label>Sub-caption</label>
          <p>A secondary description if needed</p>
      </fieldset>

      <fieldset>
          <input type="text" data-type="title" name="tags" id="tags" placeholder="house,dog,man walking" value="<?=(isset($data) && $data != ""?$data['tags']:"")?>" />
          <!-- <label>Tags</label> -->
          <p>Pressing enter or tab to add the value to list of tags</p>
      </fieldset>

    </aside>

    <aside>

      <div class="uploadbox">
        <?php
          if (isset($data['filetype'])) {
            $thumbnail = '';
            switch($data['filetype']) {
              case "link":  // allows for both default link thumbnail or custom from upload if desired and provided
                if(isset($data['filepath']) && isset($data['name'])) {
                  $thumbnail = [$data['link'],"\"../_uploads/".$data['name']."\"",""];
                } else {
                  $thumbnail = [$data['link'],"\"ui/linkicon.jpg\"",""];
                }
                break;
              case "vr":
                if(isset($data['filepath']) && isset($data['name'])) {
                  $thumbnail = [$data['link'],"\"../_uploads/".str_replace(".","-thumbnail.",$data['name'])."\"","contain"];
                } else {
                  $thumbnail = [$data['link'],"\"ui/vricon.jpg\"","contain"];
                }
                break;
              case "pdf":
                $thumbnail = ["\"../_uploads/".$data['filepath'].".".$data['filetype']."\"","\"ui/pdficon.jpg\"","contain"];
                break;
              case "zip":
                $thumbnail = ["\"../_uploads/".$data['filepath'].".".$data['filetype']."\"","\"ui/zipicon.jpg\"","contain"];
                break;
              case "mov":
                if(isset($data['filepath']) && isset($data['name'])) {
                  $thumbnail = [$data['link'],"\"../_uploads/".str_replace(".","-thumbnail.",$data['name'])."\"","contain"];
                } else {
                  $thumbnail = [$data['link'],"\"ui/videoicon.jpg\"","contain"];
                }
                break;
              default: // handle an image type here
                $thumbnail = ["\"../_uploads/".$data['filepath'].".".$data['filetype']."\"","\"../_uploads/".$data['filepath'].".".$data['filetype']."\"",""];
                break;
            }
          }
        ?>

        <?php if(isset($thumbnail) && !empty($thumbnail)) : ?>
          <div class="js--image-preview js--no-default <?=$thumbnail[2]; ?>" style='background-image:url(<?=$thumbnail[1]; ?>)'></div>
        <?php else : ?>
          <div class="js--image-preview"></div>
        <?php endif; ?>

        <!-- <div class="upload-options">
          <label>
            <input type="file" class="image-upload" accept="image/*" data-type="filepath" name="filetoupload" id="filetoupload" <?=(isset($data)?"disabled=\"disabled\"":"")?> />
            <div id="file-drag" style="width: 100%; height: 50px; border: 2px dotted #ffffff; text-align: center;">
              <div>
                <div style="margin-top: 12.5%;">Drag to me!</div>
              </div>
            </div>
          </label>
        </div> -->
      </div>
      <label class="file">
        Drop a file or click to select one
        <!-- <input type="file"   class="inputFile" > -->
        <input type="file" class="image-upload inputFile" accept="image/*" data-type="filepath" name="filetoupload" id="filetoupload" <?=(isset($data)?"disabled=\"disabled\"":"")?> multiple />
        <!-- use multiple, even if it’s not allowed, to be able to show an info text -->
      </label>

      <fieldset>
        <p class="uploadmessage">The file to upload (<?=$max_size?>MB max file size of types: <?=strtoupper($supportedFileTypes)?>), not required if linking to external resource</p>
      </fieldset>
      <!-- <label>File To Upload</label> -->
      <!-- <input type="file" data-type="filepath" name="filetoupload" id="filetoupload" <?=(isset($data)?"disabled=\"disabled\"":"")?> /> -->

    </aside>



    <fieldset>
        <label id="messages">&nbsp;</label>
    </fieldset>
    <fieldset class="save">
        <?php if($_GET['action'] != "edit" && !isset($_GET['full'])){ ?><input id="media_cancel" name="media_cancel" type="button" value="Cancel" data-type="tool_cancel" title="Click here to cancel"><?php } ?>
        <!-- <input type="submit" value="Save <?=(isset($_GET['rid']) && $_GET['rid'] != ""?"Changes":"Media")?>" name="media_submit" id="media_submit" data-type="tool_submit" title="Click here to save media" /> -->
        <input type="submit" value="<?=(isset($_GET['rid']) && $_GET['rid'] != ""?"Save Changes":"Upload")?>" name="media_submit" id="media_submit" data-type="tool_submit" title="Click here to <?=(isset($_GET['rid']) && $_GET['rid'] != ""?"save changes":"upload")?>" />
    </fieldset>


</form>
