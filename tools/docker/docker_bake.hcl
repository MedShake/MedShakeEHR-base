group "default" {
  targets = ["production", "development"]
}

target "production" {
  context = "./"
  dockerfile = "Dockerfile"
  platforms = ["linux/amd64", "linux/arm64"]
  args = {
    PHPSTAGE = "production"
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
    PHPSTAGE = "development"
    VRELEASE = "master"
  }
  tags = [
    "marsante/msehr:dev"
  ]
}