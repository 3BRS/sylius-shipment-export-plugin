#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# project root
cd "$(dirname "$DIR")"

set -x

# to avoid crash
# [PHPStan\Symfony\XmlContainerNotExistsException]
# Container /srv/sylius/tests/Application/var/cache/dev/Tests_ThreeBRS_SyliusShipmentExportPlugin_KernelDevDebugContainer.xml does not exist
if [ ! -f tests/Application/var/cache/dev/Tests_ThreeBRS_SyliusShipmentExportPlugin_KernelDevDebugContainer.xml ]; then
  php bin/console --env=dev cache:warmup --no-optional-warmers
fi

XDEBUG_MODE=off php -d memory_limit=1G vendor/bin/phpstan analyse \
    --level max \
    src \
    "$@"
