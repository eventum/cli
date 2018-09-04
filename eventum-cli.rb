class EventumCli < Formula
  desc "Eventum CLI Application"
  homepage "https://github.com/eventum/cli"
  url "https://github.com/eventum/cli/releases/download/0.1.0/eventum.phar"
  sha256 "56614f95aeee07b2cadbebb8bd7968364d8df56811cc65a32b579c664e8fd980"
  head "https://github.com/eventum/cli"

  bottle :unneeded

  def install
    bin.install "eventum.phar" => "eventum"
  end
end

# vim:ts=2:sw=2:et
