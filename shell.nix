{ pkgs ? import <nixpkgs> {} }:

let
  phpEnv = pkgs.php84.buildEnv {
    extensions = { enabled, all }: enabled ++ (with all; [
      gd
      intl
      mbstring
      pdo_mysql
      zip
    ]);
  };
in
pkgs.mkShell {
  packages = with pkgs; [
    phpEnv
    phpEnv.packages.composer

    symfony-cli

    nodejs_latest
  ];
}
