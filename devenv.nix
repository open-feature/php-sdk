{ pkgs, ... }:

{

  # https://devenv.sh/packages/
  packages = [ pkgs.git ];

  # https://devenv.sh/languages/
  languages.nix.enable = true;
  languages.php.enable = true;
  languages.php.package = pkgs.php80;

  # https://devenv.sh/basics/
  env.PROJECT_NAME = "openfeature-php-sdk";

  # https://devenv.sh/scripts/
  scripts.hello.exec = "echo $ Started devenv shell in $PROJECT_NAME";

  enterShell = ''
    hello
    echo
    git --version
    php --version
    echo

    # optimization step -- files and directories that match entries
    # in the .gitignore will still be traversed, and the .devenv
    # directory contains over 5000 files and 121MB.
    if ! grep -E "excludesfile.+\.gitignore" .git/config &>/dev/null
    then
      git config --local core.excludesfile .gitignore
    fi
  '';

  ## https://devenv.sh/pre-commit-hooks/
  pre-commit.hooks = {
    # # general formatting
    # prettier.enable = true;
    # github actions
    actionlint.enable = true;
    # nix
    deadnix.enable = true;
    nixfmt.enable = true;
    # php
    phpcbf.enable = true;
    # # ensure Markdown code is executable
    # mdsh.enable = true;
  };

  # https://devenv.sh/processes/
  # processes.ping.exec = "ping example.com";
}
