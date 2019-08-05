class EventumCli < Formula
  desc "Eventum CLI Application"
  homepage "https://github.com/eventum/cli"
  url "https://github.com/eventum/cli/releases/download/0.1.2/eventum.phar"
  sha256 "b0b280f990a8bbaa0a709ebbac9bbd73e544f4e3c2bba726dd497c922f54b59d"
  head "https://github.com/eventum/cli"

  bottle :unneeded

  def install
    bin.install "eventum.phar" => "eventum"
  end
end

# vim:ts=2:sw=2:et
