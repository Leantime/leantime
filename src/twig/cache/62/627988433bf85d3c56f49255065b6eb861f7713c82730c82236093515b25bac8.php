<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* app.twig */
class __TwigTemplate_5507a6be5dc5dcf875bb10a9866186642d4b9e3e9c7c7e3d45dd1967a0e8419b extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'head' => [$this, 'block_head'],
            'leftpanel' => [$this, 'block_leftpanel'],
            'leftmenu' => [$this, 'block_leftmenu'],
            'header' => [$this, 'block_header'],
            'headerInner' => [$this, 'block_headerInner'],
            'content' => [$this, 'block_content'],
            'pageheader' => [$this, 'block_pageheader'],
            'maincontent' => [$this, 'block_maincontent'],
            'scripts' => [$this, 'block_scripts'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<!DOCTYPE html>
<html dir=\"";
        // line 2
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["language"] ?? null), "direction", [], "any", false, false, false, 2), "html", null, true);
        echo "\" lang=\"";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["language"] ?? null), "code", [], "any", false, false, false, 2), "html", null, true);
        echo "\">
<head>
    ";
        // line 4
        $this->displayBlock('head', $context, $blocks);
        // line 7
        echo "</head>

<body>
    <div class=\"mainwrapper ";
        // line 10
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["mainwrapper"] ?? null), "classes", [], "any", false, false, false, 10), "html", null, true);
        echo "\">

        <div class=\"leftpanel ";
        // line 12
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["leftpanel"] ?? null), "classes", [], "any", false, false, false, 12), "html", null, true);
        echo "\" ";
        if ((twig_get_attribute($this->env, $this->source, ($context["menu"] ?? null), "state", [], "any", false, false, false, 12) == "closed")) {
            echo " style=\"margin-left:-240px\" ";
        }
        echo ">
            ";
        // line 13
        $this->displayBlock('leftpanel', $context, $blocks);
        // line 28
        echo "        </div><!-- leftpanel -->

        <div class=\"header\" ";
        // line 30
        if ((twig_get_attribute($this->env, $this->source, ($context["menu"] ?? null), "state", [], "any", false, false, false, 30) == "closed")) {
            echo " style=\"margin-left:0px; width:100%\" ";
        }
        echo ">
            ";
        // line 31
        $this->displayBlock('header', $context, $blocks);
        // line 42
        echo "        </div>

        <div class=\"rightpanel\" style=\"position: relative; ";
        // line 44
        (((twig_get_attribute($this->env, $this->source, ($context["menu"] ?? null), "state", [], "any", false, false, false, 44) == "closed")) ? (print (twig_escape_filter($this->env, twig_escape_filter($this->env, "margin-left:0px;", "css"), "html", null, true))) : (print ("")));
        echo "\">
            ";
        // line 45
        $this->displayBlock('content', $context, $blocks);
        // line 57
        echo "        </div><!--rightpanel-->

    </div><!--mainwrapper-->

";
        // line 61
        echo twig_escape_filter($this->env, $this->env->getFunction('includeRoute')->getCallable()("general.pageBottom"), "html", null, true);
        echo "
";
        // line 62
        $this->displayBlock('scripts', $context, $blocks);
    }

    // line 4
    public function block_head($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 5
        echo "        ";
        echo twig_escape_filter($this->env, $this->env->getFunction('includeRoute')->getCallable()("general.header"), "html", null, true);
        echo "
    ";
    }

    // line 13
    public function block_leftpanel($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 14
        echo "                <a class=\"barmenu ";
        echo (((twig_get_attribute($this->env, $this->source, ($context["menu"] ?? null), "state", [], "any", false, false, false, 14) == "open")) ? ("open") : (""));
        echo "\" href=\"javascript:void(0);\">
                    <span class=\"fa fa-bars\"></span>
                </a>

                <div class=\"logo\"  ";
        // line 18
        if ((twig_get_attribute($this->env, $this->source, ($context["menu"] ?? null), "state", [], "any", false, false, false, 18) == "closed")) {
            echo " style=\"margin-left:-260px\" ";
        }
        echo ">
                    <a href=\"";
        // line 19
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "base_url", [], "any", false, false, false, 19), "html", null, true);
        echo "\" style=\"background-image:url(";
        echo twig_escape_filter($this->env, twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["companysettings"] ?? null), "logoPath", [], "any", false, false, false, 19), "css"), "html", null, true);
        echo ")\">&nbsp;</a>
                </div>

                <div class=\"leftmenu\">
                    ";
        // line 23
        $this->displayBlock('leftmenu', $context, $blocks);
        // line 26
        echo "                </div><!--leftmenu-->
            ";
    }

    // line 23
    public function block_leftmenu($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 24
        echo "                        ";
        echo twig_escape_filter($this->env, $this->env->getFunction('includeRoute')->getCallable()("general.menu"), "html", null, true);
        echo "
                    ";
    }

    // line 31
    public function block_header($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 32
        echo "                <div class=\"headerinner\" ";
        if ((twig_get_attribute($this->env, $this->source, ($context["menu"] ?? null), "state", [], "any", false, false, false, 32) == "closed")) {
            echo " style=\"margin-left:0px\" ";
        }
        echo ">
                    ";
        // line 33
        $this->displayBlock('headerInner', $context, $blocks);
        // line 40
        echo "                </div>
            ";
    }

    // line 33
    public function block_headerInner($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 34
        echo "                        <div class=\"userloggedinfo\">
                            ";
        // line 35
        echo twig_escape_filter($this->env, $this->env->getFunction('includeRoute')->getCallable()("general.loginInfo"), "html", null, true);
        echo "
                        </div>

                        ";
        // line 38
        echo twig_escape_filter($this->env, $this->env->getFunction('includeRoute')->getCallable()("general.headMenu"), "html", null, true);
        echo "
                    ";
    }

    // line 45
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 46
        echo "
                ";
        // line 47
        $this->displayBlock('pageheader', $context, $blocks);
        // line 48
        echo "
                <div class=\"maincontent\">
                    ";
        // line 50
        $this->displayBlock('maincontent', $context, $blocks);
        // line 53
        echo "                </div> <!-- End .maincontent -->

                ";
        // line 55
        echo twig_escape_filter($this->env, $this->env->getFunction('includeRoute')->getCallable()("general.footer"), "html", null, true);
        echo "
            ";
    }

    // line 47
    public function block_pageheader($context, array $blocks = [])
    {
        $macros = $this->macros;
    }

    // line 50
    public function block_maincontent($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 51
        echo "                        <!--###MAINCONTENT###-->
                    ";
    }

    // line 62
    public function block_scripts($context, array $blocks = [])
    {
        $macros = $this->macros;
    }

    public function getTemplateName()
    {
        return "app.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  247 => 62,  242 => 51,  238 => 50,  232 => 47,  226 => 55,  222 => 53,  220 => 50,  216 => 48,  214 => 47,  211 => 46,  207 => 45,  201 => 38,  195 => 35,  192 => 34,  188 => 33,  183 => 40,  181 => 33,  174 => 32,  170 => 31,  163 => 24,  159 => 23,  154 => 26,  152 => 23,  143 => 19,  137 => 18,  129 => 14,  125 => 13,  118 => 5,  114 => 4,  110 => 62,  106 => 61,  100 => 57,  98 => 45,  94 => 44,  90 => 42,  88 => 31,  82 => 30,  78 => 28,  76 => 13,  68 => 12,  63 => 10,  58 => 7,  56 => 4,  49 => 2,  46 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "app.twig", "/Users/josephroberts/localdev/leantime/public/theme/default/layout/app.twig");
    }
}
