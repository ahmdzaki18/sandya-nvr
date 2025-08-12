<?php
namespace App\Libraries;

class LdapLib
{
    private string $ldap_host = 'dc.sandya.net';
    private int    $ldap_port = 389;
    private string $base_dn   = 'CN=Users,DC=sandya,DC=net';
    private string $domain    = '@sandya.net';

    public function login(string $username, string $password): array|false
    {
        $conn = ldap_connect($this->ldap_host, $this->ldap_port);
        if (!$conn) return false;
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        $bindUser = $username . $this->domain;

        if (@ldap_bind($conn, $bindUser, $password)) {
            $filter = "(sAMAccountName=$username)";
            $result = ldap_search($conn, $this->base_dn, $filter);
            $entries = ldap_get_entries($conn, $result);
            ldap_unbind($conn);
            return [
                'username'     => $username,
                'nama_lengkap' => $entries[0]['cn'][0]   ?? $username,
                'email'        => $entries[0]['mail'][0] ?? null,
                'dn'           => $entries[0]['distinguishedname'][0] ?? null,
            ];
        }
        ldap_unbind($conn);
        return false;
    }
}
