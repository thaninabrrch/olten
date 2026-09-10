{{--
    Filet de separation entre deux blocs du corps.
    Une cellule de 1px plutot qu'un <hr> : les clients mail habillent le <hr>
    chacun a leur facon (relief, marges, couleur systeme).
--}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; margin:4px 0 24px;">
    <tr>
        <td height="1" bgcolor="{{ config('olten.email.colors.line') }}" style="height:1px; background-color:{{ config('olten.email.colors.line') }}; font-size:0; line-height:0;">&nbsp;</td>
    </tr>
</table>
