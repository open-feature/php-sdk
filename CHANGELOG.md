# Changelog

## [1.1.2](https://github.com/open-feature/php-sdk/compare/1.1.1...1.1.2) (2022-12-21)


### Miscellaneous Chores

* add OpenSSF badge to README ([384242f](https://github.com/open-feature/php-sdk/commit/384242f11faaffe8d671f2182b888b15b9458ebd))

## [1.1.1](https://github.com/open-feature/php-sdk/compare/1.1.0...1.1.1) (2022-12-21)


### Bug Fixes

* **deps:** update dependency open-feature/flagd-provider to ^0.1.0 ([#35](https://github.com/open-feature/php-sdk/issues/35)) ([928dc5f](https://github.com/open-feature/php-sdk/commit/928dc5f8e1e74ca10c749b3975be67408e6ca21e))


### Miscellaneous Chores

* **deps:** update amannn/action-semantic-pull-request action to v5 ([#37](https://github.com/open-feature/php-sdk/issues/37)) ([6bc2ec8](https://github.com/open-feature/php-sdk/commit/6bc2ec8998fe12e9357f43609079f80caba51172))
* **deps:** update dependency psalm/plugin-mockery to ^0.11.0 ([#30](https://github.com/open-feature/php-sdk/issues/30)) ([432bfb7](https://github.com/open-feature/php-sdk/commit/432bfb722f2e3de61167375c369b8e14d31f7464))
* **deps:** update dependency psalm/plugin-mockery to v1 ([#38](https://github.com/open-feature/php-sdk/issues/38)) ([9449851](https://github.com/open-feature/php-sdk/commit/9449851b51a3dd6a37c0a4bf0ed24381c4a4a9e1))
* **deps:** update dependency psalm/plugin-phpunit to ^0.18.0 ([#31](https://github.com/open-feature/php-sdk/issues/31)) ([716930c](https://github.com/open-feature/php-sdk/commit/716930c1626e1be4f86fd0ad1dc3ade700ae1d4d))
* **deps:** update dependency vimeo/psalm to v5 ([#39](https://github.com/open-feature/php-sdk/issues/39)) ([4aec058](https://github.com/open-feature/php-sdk/commit/4aec058538f4845326079040a10e4079c42fe2cd))

## [1.1.0](https://github.com/open-feature/php-sdk/compare/v1.0.1...1.1.0) (2022-12-19)


### Features

* provide a base AbstractProvider for provider implementations to use ([6ffffe9](https://github.com/open-feature/php-sdk/commit/6ffffe9a767723c424e112e27bdd4bd0508d9f7d))
* support for ErrorCode enum, Reason strings with pre-existing consts, v0.5 spec ([37f13e1](https://github.com/open-feature/php-sdk/commit/37f13e1d4c951473243cfe9d73f43cc3cd188fae))
* support ResolutionError, init test-harness ([784d706](https://github.com/open-feature/php-sdk/commit/784d706145accaa4f45369fed43561ceec00df92))


### Bug Fixes

* exclude v in tag ([#8](https://github.com/open-feature/php-sdk/issues/8)) ([1ecabdb](https://github.com/open-feature/php-sdk/commit/1ecabdbf216139a65ed7be81561f32b078749489))
* excludes v from tag version ([#6](https://github.com/open-feature/php-sdk/issues/6)) ([82f587d](https://github.com/open-feature/php-sdk/commit/82f587d32b8fd7d8320253180098424b782cd943))
* extend Exception for ResolutionError and provide non-overlapping methods ([ee75354](https://github.com/open-feature/php-sdk/commit/ee753544e2e4467df341175646f54d00e9c1c8ab))


### Miscellaneous Chores

* bump release config to latest semver  ([644a9f8](https://github.com/open-feature/php-sdk/commit/644a9f8ac3b1b5a66cb5791b0fad1a18e61aaf15))
* Configure Renovate ([#12](https://github.com/open-feature/php-sdk/issues/12)) ([e2d2603](https://github.com/open-feature/php-sdk/commit/e2d26032621bacb678d69b84e970002c2a5afe74))
* **deps:** update dependency php to v8.2.0 ([#24](https://github.com/open-feature/php-sdk/issues/24)) ([b9e0985](https://github.com/open-feature/php-sdk/commit/b9e098577f56eafbe6017a39954013c7e7521bfc))
* **deps:** update dependency phpstan/phpstan to ~1.9.0 ([#25](https://github.com/open-feature/php-sdk/issues/25)) ([2656740](https://github.com/open-feature/php-sdk/commit/265674015bbd6a58b68a1efa7e2c928a41e1f9c3))
* **main:** release 0.0.10 ([#23](https://github.com/open-feature/php-sdk/issues/23)) ([470aa01](https://github.com/open-feature/php-sdk/commit/470aa01c50458e1427923dfd1d0d17ff0b4b7063))
* **main:** release 0.0.2 ([#3](https://github.com/open-feature/php-sdk/issues/3)) ([01b6a23](https://github.com/open-feature/php-sdk/commit/01b6a234ec43e5372fda3cd6ace882dda4110422))
* **main:** release 0.0.3 ([#7](https://github.com/open-feature/php-sdk/issues/7)) ([6fb4118](https://github.com/open-feature/php-sdk/commit/6fb411866dc592b83f309d3bb88b38b30e453379))
* **main:** release 0.0.4 ([#9](https://github.com/open-feature/php-sdk/issues/9)) ([65c92d9](https://github.com/open-feature/php-sdk/commit/65c92d99ddc22e853a33d4a63fb9d9b61f56787e))
* **main:** release 0.0.5 ([#11](https://github.com/open-feature/php-sdk/issues/11)) ([2344c4a](https://github.com/open-feature/php-sdk/commit/2344c4ae15ebf5e8a3cf22f3c5d9bdaeba2a4119))
* **main:** release 0.0.6 ([#13](https://github.com/open-feature/php-sdk/issues/13)) ([5afadc7](https://github.com/open-feature/php-sdk/commit/5afadc7fe9d376e47f0b262dac42025057f363de))
* **main:** release 0.0.7 ([#14](https://github.com/open-feature/php-sdk/issues/14)) ([6ecd918](https://github.com/open-feature/php-sdk/commit/6ecd918d18afa21f4939da432fb162d058470004))
* **main:** release 0.0.8 ([#15](https://github.com/open-feature/php-sdk/issues/15)) ([7d7f1d2](https://github.com/open-feature/php-sdk/commit/7d7f1d2ce5b7ace4abc7f6c778c3d02196b7e632))
* **main:** release 0.0.9 ([#22](https://github.com/open-feature/php-sdk/issues/22)) ([c1b0675](https://github.com/open-feature/php-sdk/commit/c1b067541b6fb7f9d07fecbe27c9e3dc8d2b2aae))
* remove redundant exclusion ([#10](https://github.com/open-feature/php-sdk/issues/10)) ([ba4b5f1](https://github.com/open-feature/php-sdk/commit/ba4b5f15eb8984af57eb08e7bb9c903671366789))
* Update README.md ([#21](https://github.com/open-feature/php-sdk/issues/21)) ([702df0c](https://github.com/open-feature/php-sdk/commit/702df0c121a54f43deb0281e7283083b8b685fe2))

## [0.0.10](https://github.com/open-feature/php-sdk/compare/0.0.9...0.0.10) (2022-12-19)


### Miscellaneous Chores

* Configure Renovate ([#12](https://github.com/open-feature/php-sdk/issues/12)) ([e2d2603](https://github.com/open-feature/php-sdk/commit/e2d26032621bacb678d69b84e970002c2a5afe74))
* **deps:** update dependency php to v8.2.0 ([#24](https://github.com/open-feature/php-sdk/issues/24)) ([b9e0985](https://github.com/open-feature/php-sdk/commit/b9e098577f56eafbe6017a39954013c7e7521bfc))
* **deps:** update dependency phpstan/phpstan to ~1.9.0 ([#25](https://github.com/open-feature/php-sdk/issues/25)) ([2656740](https://github.com/open-feature/php-sdk/commit/265674015bbd6a58b68a1efa7e2c928a41e1f9c3))

## [0.0.9](https://github.com/open-feature/php-sdk/compare/0.0.8...0.0.9) (2022-12-19)


### Miscellaneous Chores

* Update README.md ([#21](https://github.com/open-feature/php-sdk/issues/21)) ([702df0c](https://github.com/open-feature/php-sdk/commit/702df0c121a54f43deb0281e7283083b8b685fe2))

## [0.0.8](https://github.com/open-feature/php-sdk/compare/0.0.7...0.0.8) (2022-11-03)


### Features

* provide a base AbstractProvider for provider implementations to use ([6ffffe9](https://github.com/open-feature/php-sdk/commit/6ffffe9a767723c424e112e27bdd4bd0508d9f7d))
* support for ErrorCode enum, Reason strings with pre-existing consts, v0.5 spec ([37f13e1](https://github.com/open-feature/php-sdk/commit/37f13e1d4c951473243cfe9d73f43cc3cd188fae))
* support ResolutionError, init test-harness ([784d706](https://github.com/open-feature/php-sdk/commit/784d706145accaa4f45369fed43561ceec00df92))


### Bug Fixes

* exclude v in tag ([#8](https://github.com/open-feature/php-sdk/issues/8)) ([1ecabdb](https://github.com/open-feature/php-sdk/commit/1ecabdbf216139a65ed7be81561f32b078749489))
* excludes v from tag version ([#6](https://github.com/open-feature/php-sdk/issues/6)) ([82f587d](https://github.com/open-feature/php-sdk/commit/82f587d32b8fd7d8320253180098424b782cd943))
* extend Exception for ResolutionError and provide non-overlapping methods ([ee75354](https://github.com/open-feature/php-sdk/commit/ee753544e2e4467df341175646f54d00e9c1c8ab))


### Miscellaneous Chores

* add CODEOWNERS file for review flow ([48b8e57](https://github.com/open-feature/php-sdk/commit/48b8e57ddb4c61aaae8259949148e762bd646bc2))
* **main:** release 0.0.2 ([#3](https://github.com/open-feature/php-sdk/issues/3)) ([01b6a23](https://github.com/open-feature/php-sdk/commit/01b6a234ec43e5372fda3cd6ace882dda4110422))
* **main:** release 0.0.3 ([#7](https://github.com/open-feature/php-sdk/issues/7)) ([6fb4118](https://github.com/open-feature/php-sdk/commit/6fb411866dc592b83f309d3bb88b38b30e453379))
* **main:** release 0.0.4 ([#9](https://github.com/open-feature/php-sdk/issues/9)) ([65c92d9](https://github.com/open-feature/php-sdk/commit/65c92d99ddc22e853a33d4a63fb9d9b61f56787e))
* **main:** release 0.0.5 ([#11](https://github.com/open-feature/php-sdk/issues/11)) ([2344c4a](https://github.com/open-feature/php-sdk/commit/2344c4ae15ebf5e8a3cf22f3c5d9bdaeba2a4119))
* **main:** release 0.0.6 ([#13](https://github.com/open-feature/php-sdk/issues/13)) ([5afadc7](https://github.com/open-feature/php-sdk/commit/5afadc7fe9d376e47f0b262dac42025057f363de))
* **main:** release 0.0.7 ([#14](https://github.com/open-feature/php-sdk/issues/14)) ([6ecd918](https://github.com/open-feature/php-sdk/commit/6ecd918d18afa21f4939da432fb162d058470004))
* remove redundant exclusion ([#10](https://github.com/open-feature/php-sdk/issues/10)) ([ba4b5f1](https://github.com/open-feature/php-sdk/commit/ba4b5f15eb8984af57eb08e7bb9c903671366789))
* rename package under `open-feature` umbrella ([df431c2](https://github.com/open-feature/php-sdk/commit/df431c2fb3f74af8c9731e8212e973bb87de4a28))

## [0.0.7](https://github.com/open-feature/php-sdk/compare/0.0.6...0.0.7) (2022-11-01)


### Bug Fixes

* extend Exception for ResolutionError and provide non-overlapping methods ([541ebc4](https://github.com/open-feature/php-sdk/commit/541ebc4f416b1aeff3b35d784513895697894e4e))

## [0.0.6](https://github.com/open-feature/php-sdk/compare/0.0.5...0.0.6) (2022-10-30)


### Features

* provide a base AbstractProvider for provider implementations to use ([78be67c](https://github.com/open-feature/php-sdk/commit/78be67cd93719742e0e0169a6b2ff9bffe95086b))

## [0.0.5](https://github.com/open-feature/php-sdk/compare/0.0.4...0.0.5) (2022-10-21)


### Miscellaneous Chores

* remove redundant exclusion ([#10](https://github.com/open-feature/php-sdk/issues/10)) ([ba4b5f1](https://github.com/open-feature/php-sdk/commit/ba4b5f15eb8984af57eb08e7bb9c903671366789))

## [0.0.4](https://github.com/open-feature/php-sdk/compare/0.0.3...0.0.4) (2022-10-21)


### Bug Fixes

* exclude v in tag ([#8](https://github.com/open-feature/php-sdk/issues/8)) ([1ecabdb](https://github.com/open-feature/php-sdk/commit/1ecabdbf216139a65ed7be81561f32b078749489))

## [0.0.3](https://github.com/open-feature/php-sdk/compare/v0.0.2...0.0.3) (2022-10-21)


### Bug Fixes

* excludes v from tag version ([#6](https://github.com/open-feature/php-sdk/issues/6)) ([82f587d](https://github.com/open-feature/php-sdk/commit/82f587d32b8fd7d8320253180098424b782cd943))

## [0.0.2](https://github.com/open-feature/php-sdk/compare/v0.0.1...v0.0.2) (2022-10-21)


### Features

* support for ErrorCode enum, Reason strings with pre-existing consts, v0.5 spec ([37f13e1](https://github.com/open-feature/php-sdk/commit/37f13e1d4c951473243cfe9d73f43cc3cd188fae))
* support ResolutionError, init test-harness ([784d706](https://github.com/open-feature/php-sdk/commit/784d706145accaa4f45369fed43561ceec00df92))
