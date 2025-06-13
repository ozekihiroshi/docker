<?php
$ldap_servers = [
    "staff" => [
        "url" => "ldaps://staffdc2.gtc.ce.ac.bw:636",
        "base_dn" => "DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw",
        "bind_dn" => "CN=administrator,CN=users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw",
        "bind_pw" => "Password1"
    ],
    "students" => [
        "url" => "ldaps://studentsdc2.gtc.ce.ac.bw:636",
        "base_dn" => "DC=students,DC=gtc,DC=ce,DC=ac,DC=bw",
        "bind_dn" => "CN=administrator,CN=users,DC=students,DC=gtc,DC=ce,DC=ac,DC=bw",
        "bind_pw" => "Password1"
    ]
];

// 管理者バインド情報（共通）
$ldap_binddn = "CN=Administrator,CN=Users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
$ldap_bindpw = "Password1";

