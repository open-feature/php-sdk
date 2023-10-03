{
  description = "OpenFeature PHP SDK Nix flake dev shells";

  # Flake inputs
  inputs.nixpkgs.url = "github:NixOS/nixpkgs";

  # Flake outputs
  outputs = { self, nixpkgs }:
    let
      # Systems supported
      allSystems = [
        "x86_64-linux" # 64-bit Intel/ARM Linux
        "aarch64-linux" # 64-bit AMD Linux
        "x86_64-darwin" # 64-bit Intel/ARM macOS
        "aarch64-darwin" # 64-bit Apple Silicon
      ];

      # Helper to provide system-specific attributes
      nameValuePair = name: value: { inherit name value; };
      genAttrs = names: f: builtins.listToAttrs (map (n: nameValuePair n (f n)) names);
      forAllSystems = f: genAttrs allSystems (system: f {
        pkgs = import nixpkgs {
          inherit system;
        };
      });
    in
    {
      # Development environment output
      devShells = forAllSystems ({ pkgs }:
        let
          coreShellPackages = [
            pkgs.zsh
          ];
          coreDevPackages = [
            pkgs.git
          ];
          corePhpPackages = [
            pkgs.libpng
          ];
          php80Packages = [
            pkgs.php80
            pkgs.php80.packages.composer
          ];
          php81Packages = [
            pkgs.php81
            pkgs.php81.packages.composer
          ];
          php82Packages = [
            pkgs.php82
            pkgs.php82.packages.composer
          ];
          emptyStr = "";
          shellHookCommandFactory = { git ? true, php ? false, node ? false, yarn ? false, pnpm ? false, python ? false, bun ? false }: ''
            echo $ Started devenv shell for OpenFeature PHP-SDK with $PHP_VERSION
            echo
            ${if git    then ''git --version''                             else emptyStr}
            ${if php    then ''php --version''                             else emptyStr}
            echo
          '';
          phpShellHookCommand = shellHookCommandFactory { php = true; };
        in rec
        {
          ### Generic language shells (NodeJS, PHP, etc.)

          php80 = pkgs.mkShell {
            packages = coreShellPackages ++ coreDevPackages ++ corePhpPackages ++ php80Packages;

            PHP_VERSION = "PHP80";

            shellHook = phpShellHookCommand;
          };

          php81 = pkgs.mkShell {
            packages = coreShellPackages ++ coreDevPackages ++ corePhpPackages ++ php81Packages;

            PHP_VERSION = "PHP81";

            shellHook = phpShellHookCommand;
          };

          php82 = pkgs.mkShell {
            packages = coreShellPackages ++ coreDevPackages ++ corePhpPackages ++ php82Packages;

            PHP_VERSION = "PHP82";

            shellHook = phpShellHookCommand;
          };

          # Default aliases
          php = php82;

          default = php;
        }
      );
    };
}