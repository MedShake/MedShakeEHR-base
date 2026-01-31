group "default" {
  targets = ["production", "development"]
}

target "production" {
  context = "./"
  dockerfile = "Dockerfile"
  platforms = ["linux/amd64", "linux/arm64"]
  args = {
    PHP_VERSION = "8.4"
    PHPSTAGE = "production"
    COMPOSER_VERSION = "2.8"
    VRELEASE = "master"
  }
  tags = [
    "marsante/msehr:master",
    "marsante/msehr:latest"
  ]
}

target "development" {
  context = "./"
  dockerfile = "Dockerfile"
  platforms = ["linux/amd64", "linux/arm64"]
  args = {
    PHP_VERSION = "8.5"
    PHPSTAGE = "development"
    COMPOSER_VERSION = "2.8"
    VRELEASE = "dev"
  }
  tags = [
    "marsante/msehr:dev"
  ]
}