class EventumCli < Formula
  desc "Eventum CLI Application"
  homepage "https://github.com/eventum/cli"
  url "https://github.com/eventum/cli/releases/download/0.1.1/eventum.phar"
  sha256 "00379c8e6da03a36cfbb37860006c2cb244932b04cac49ebfe02daaffbc983fc"
  head "https://github.com/eventum/cli"

  bottle :unneeded

  def install
    bin.install "eventum.phar" => "eventum"
  end
end

# vim:ts=2:sw=2:et
