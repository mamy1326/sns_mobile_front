// 【管理画面】ユーザ情報確認からのボタン押下
function UserSubmit( ActionModule )
{
  document.user_confirm.action = ActionModule;
  document.user_confirm.confirm_return.value = '1';
  document.user_confirm.submit();
}

// 【管理画面】フォトアルバム情報確認からのボタン押下
function AlbumSubmit( ActionModule )
{
  document.album_confirm.action = ActionModule;
  document.album_confirm.confirm_return.value = '1';
  document.album_confirm.submit();
}

// 【管理画面】フォトアルバム情報確認からのボタン押下
function PhotoUploadSubmit( ActionModule )
{
  document.photo_confirm.action = ActionModule;
  document.photo_confirm.confirm_return.value = '1';
  document.photo_confirm.submit();
}

// 【管理画面】管理本部通信情報確認からのボタン押下
function InfoSubmit( ActionModule )
{
  document.info_confirm.action = ActionModule;
  document.info_confirm.confirm_return.value = '1';
  document.info_confirm.submit();
}

// 【管理画面】ユーザグループ情報確認からのボタン押下
function UserGroupSubmit( ActionModule )
{
  document.usergroup_confirm.action = ActionModule;
  document.usergroup_confirm.confirm_return.value = '1';
  document.usergroup_confirm.submit();
}

// 【管理画面】submit用
function ConfirmSubmit( ActionModule, FormName )
{
  document.forms[ FormName ].action = ActionModule;
  document.forms[ FormName ].confirm_return.value = '1';
  document.forms[ FormName ].submit();
}
