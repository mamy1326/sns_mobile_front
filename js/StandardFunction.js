// ユーザ一覧のウィンドウを開く。既に開かれていればフォーカスを当てる。
function GroupUserWindowOpen( OpenURL, OpenWiondowName, Option )
{
    NewWindow = window.open( OpenURL, OpenWiondowName, Option );
    NewWindow.window.focus();
}

// 公開レベル選択時にのみ、ユーザグループを表示する
function ShowUserGroup( FormName )
{
    // ユーザグループ選択用に表示
    if( true == document.forms[ FormName ].del_flag[1].checked )
    {
        document.getElementById("user_group").style.display = '';
        document.getElementById("user_group_title").style.display = '';
    }
    else
    {
        document.getElementById("user_group").style.display = 'none';
        document.getElementById("user_group_title").style.display = 'none';
    }
}
