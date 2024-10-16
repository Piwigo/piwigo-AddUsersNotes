{combine_script id="jquery.usersnotes" load='footer' path="{$USERNOTES_PATH}/js/usersnotes.js"}
{footer_script}
const str_notes = "{'Notes'|@translate|escape:javascript}";
{/footer_script}
<textarea id="usersnotes_textarea"></textarea>
{html_style}
  #usersnotes_textarea {
    display: none;
    width: 100%;
    height: 100%;
    resize: none;
  }
{/html_style}