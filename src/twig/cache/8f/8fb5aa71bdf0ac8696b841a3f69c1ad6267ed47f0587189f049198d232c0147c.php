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

/* home.twig */
class __TwigTemplate_84d0567e1281702d6ea665cb434d32391195f26d1786af083380ba20b8a16f3c extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'pageheader' => [$this, 'block_pageheader'],
            'maincontent' => [$this, 'block_maincontent'],
            'scripts' => [$this, 'block_scripts'],
        ];
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return $this->loadTemplate(($context["layoutFile"] ?? null), "home.twig", 1);
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_pageheader($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 4
        echo "    ";
        echo twig_escape_filter($this->env, $this->env->getFunction('dispatchEvent')->getCallable()("beforePageHeaderOpen"), "html", null, true);
        echo "
    <div class=\"pageheader\">
        ";
        // line 6
        echo twig_escape_filter($this->env, $this->env->getFunction('dispatchEvent')->getCallable()("afterPageHeaderOpen"), "html", null, true);
        echo "
        <div class=\"pageicon\"><span class=\"fa fa-home\"></span></div>
        <div class=\"pagetitle\">
            <h1>";
        // line 9
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("headlines.home"), "html", null, true);
        echo "</h1>
        </div>
        ";
        // line 11
        echo twig_escape_filter($this->env, $this->env->getFunction('dispatchEvent')->getCallable()("afterPageHeaderClose"), "html", null, true);
        echo "
    </div>
    ";
        // line 13
        echo twig_escape_filter($this->env, $this->env->getFunction('dispatchEvent')->getCallable()("afterPageHeaderClose"), "html", null, true);
        echo "
";
    }

    // line 16
    public function block_maincontent($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 17
        echo "    ";
        echo twig_escape_filter($this->env, $this->env->getFunction('displayNotification')->getCallable()(), "html", null, true);
        echo "

    <div class=\"row\">
        <div class=\"col-md-12\">
            <div class=\"maincontentinner\">
                <div class=\"row\">
                    <div class=\"col-md-12\">
                        <h3 class=\"todaysDate\" style=\"padding-bottom:5px;\"></h3>
                        <h1 class=\"articleHeadline\">";
        // line 25
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("text.hi"), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["currentUser"] ?? null), "firstname", [], "any", false, false, false, 25), "html", null, true);
        echo "</h1>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End .row -->
    <div class=\"row\">
        <div class=\"col-md-8\">
            <div class=\"maincontentinner\">
                <div class=\"row\" id=\"yourToDoContainer\">
                    <div class=\"col-md-12\">
                        ";
        // line 36
        if ((twig_get_attribute($this->env, $this->source, ($context["currentUser"] ?? null), "role", [], "any", false, false, false, 36) > twig_get_attribute($this->env, $this->source, ($context["roles"] ?? null), "editor", [], "any", false, false, false, 36))) {
            // line 37
            echo "                            <a href=\"javascript:void(0);\" class=\"quickAddLink\" id=\"ticket_new_link\" onclick=\"jQuery('#ticket_new').toggle('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).toggle('fast');\"><i class=\"fas fa-plus-circle\"></i> <?php echo \$this->__(\"links.quick_add_todo\"); ?></a>
                            <div class=\"ticketBox hideOnLoad\" id=\"ticket_new\" style=\"padding:10px;\">

                                <form method=\"post\" class=\"form-group\">
                                    <input name=\"headline\" type=\"text\" title=\"<?php echo \$this->__(\"label.headline\"); ?>\" style=\"width:100%\" placeholder=\"<?php echo \$this->__(\"input.placeholders.what_are_you_working_on\"); ?>\" />
                                    <input type=\"submit\" value=\"<?php echo \$this->__(\"buttons.save\"); ?>\" name=\"quickadd\"  />
                                    <input type=\"hidden\" name=\"dateToFinish\" id=\"dateToFinish\" value=\"\" />
                                    <input type=\"hidden\" name=\"status\" value=\"3\" />
                                    <input type=\"hidden\" name=\"sprint\" value=\"<?php echo \$_SESSION['currentSprint']; ?>\" />
                                    <a href=\"javascript:void(0);\" onclick=\"jQuery('#ticket_new').toggle('fast'); jQuery('#ticket_new_link').toggle('fast');\">
                                        <?php echo \$this->__(\"links.cancel\"); ?>
                                    </a>
                                </form>

                                <div class=\"clearfix\"></div>
                                <br /><br />
                            </div>
                        ";
        }
        // line 55
        echo "
                        <div class=\"marginBottomMd\">
                            <form method=\"get\" >
                                <div class=\"pull-left\">
                                <h5 class=\"subtitle\">";
        // line 59
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("headlines.your_todos"), "html", null, true);
        echo "</h5>
                                </div>

                                <div class=\"btn-group viewDropDown right\">
                                    <button class=\"btn dropdown-toggle \" type=\"button\" data-toggle=\"dropdown\">";
        // line 63
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("links.group_by"), "html", null, true);
        echo "</button>
                                    <ul class=\"dropdown-menu\">
                                        <li>
                                            <span class=\"radio\">
                                                <input type=\"radio\" name=\"groupBy\" ";
        // line 67
        if ((($context["groupby"] ?? null) == "time")) {
            echo " checked='checked' ";
        }
        echo " value=\"time\" id=\"groupByDate\" onclick=\"form.submit();\"/>
                                                <label for=\"groupByDate\">";
        // line 68
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("label.dates"), "html", null, true);
        echo "</label>
                                            </span>
                                        </li>
                                        <li>
                                            <span class=\"radio\">
                                                <input type=\"radio\" name=\"groupBy\" ";
        // line 73
        if ((($context["groupby"] ?? null) == "project")) {
            echo " checked='checked' ";
        }
        echo " value=\"project\" id=\"groupByProject\" onclick=\"form.submit();\"/>
                                                <label for=\"groupByProject\">";
        // line 74
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("label.project"), "html", null, true);
        echo "</label>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                                <div class=\"right\">
                                    <label class=\"inline\">";
        // line 80
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("label.show"), "html", null, true);
        echo "</label>
                                    <select name=\"projectFilter\" onchange=\"form.submit();\">
                                        <option value=\"\">";
        // line 82
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("labels.all_projects"), "html", null, true);
        echo "</option>
                                        ";
        // line 83
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["projects"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["project"]) {
            // line 84
            echo "                                            <option
                                                value=\"";
            // line 85
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["project"], "id", [], "any", false, false, false, 85), "html", null, true);
            echo "\"
                                                ";
            // line 86
            if ((($context["projectFilter"] ?? null) == twig_get_attribute($this->env, $this->source, $context["project"], "id", [], "any", false, false, false, 86))) {
                // line 87
                echo "                                                    selected=\"selected\"
                                                ";
            }
            // line 89
            echo "                                            >";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["project"], "name", [], "any", false, false, false, 89));
            echo "</option>
                                        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['project'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 91
        echo "                                    </select>
                                    &nbsp;
                                </div>
                                <div class=\"clearall\"></div>
                            </form>
                        </div>

                        ";
        // line 98
        if ( !twig_test_empty(($context["tickets"] ?? null))) {
            // line 99
            echo "                            <div class='center'>
                                <div  style='width:30%' class='svgContainer'>
                                    ";
            // line 101
            echo twig_escape_filter($this->env, $this->env->getFunction('getSvg')->getCallable()("undraw_a_moment_to_relax_bbpa.svg"), "html", null, true);
            echo "
                                </div>
                                <br />
                                <h4>";
            // line 104
            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("headlines.no_todos_this_week"), "html", null, true);
            echo "</h4>
                                ";
            // line 105
            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("text.take_the_day_off"), "html", null, true);
            echo "
                                <a href='";
            // line 106
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "base_url", [], "any", false, false, false, 106), "html", null, true);
            echo "/tickets/showAll'>";
            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("links.goto_backlog"), "html", null, true);
            echo "</a>
                                <br/><br/>
                            </div>
                        ";
        }
        // line 110
        echo "
                        ";
        // line 111
        if ( !twig_test_empty(($context["tickets"] ?? null))) {
            // line 112
            echo "                            ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["tickets"] ?? null));
            $context['loop'] = [
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            ];
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["ticketGroup"]) {
                // line 113
                echo "                                ";
                // line 114
                echo "                                ";
                $context["ticketCreationDueDate"] = (($__internal_compile_0 = (($__internal_compile_1 = twig_get_attribute($this->env, $this->source, $context["ticketGroup"], "tickets", [], "any", false, false, false, 114)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1[0] ?? null) : null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["dateToFinish"] ?? null) : null);
                // line 115
                echo "                                ";
                if (((twig_test_empty(($context["ticketCreationDueDate"] ?? null)) || (($context["ticketCreationDueDate"] ?? null) == "0000-00-00 00:00:00")) || (($context["ticketCreationDueDate"] ?? null) == "1969-12-31 00:00:00"))) {
                    // line 116
                    echo "                                    ";
                    $context["ticketCreationDueDate"] = "";
                    // line 117
                    echo "                                ";
                }
                // line 118
                echo "                                ";
                $context["groupProjectId"] = ((((($context["groupBy"] ?? null) == "projects") &&  !twig_test_empty(twig_first($this->env, twig_get_attribute($this->env, $this->source, $context["ticketGroup"], "tickets", [], "any", false, false, false, 118))))) ? ((($__internal_compile_2 = (($__internal_compile_3 = twig_get_attribute($this->env, $this->source, $context["ticketGroup"], "tickets", [], "any", false, false, false, 118)) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3[0] ?? null) : null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["projectId"] ?? null) : null)) : (twig_get_attribute($this->env, $this->source, ($context["session"] ?? null), "currentProject", [], "any", false, false, false, 118)));
                // line 119
                echo "                                ";
                // line 120
                echo "                                <a class=\"anchor\" id=\"accordion_anchor_";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 120), "html", null, true);
                echo "\"></a>
                                <h5 class=\"accordionTitle\" id=\"accordion_link_";
                // line 121
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 121), "html", null, true);
                echo "\">
                                    <a href=\"javascript:void(0)\" class=\"accordion-toggle\" id=\"accordion_toggle_";
                // line 122
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 122), "html", null, true);
                echo "\" onclick=\"accordionToggle('<?=\$i ?>');\">
                                        <i class=\"fa fa-angle-down\"></i>";
                // line 123
                echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()(twig_get_attribute($this->env, $this->source, $context["ticketGroup"], "labelName", [], "any", false, false, false, 123)), "html", null, true);
                echo "
                                        (";
                // line 124
                echo twig_escape_filter($this->env, twig_length_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ticketGroup"], "tickets", [], "any", false, false, false, 124)), "html", null, true);
                echo ")
                                    </a>
                                    <a
                                        class=\"titleInsertLink\"
                                        href=\"javascript:void(0)\"
                                        onclick=\"insertQuickAddForm(
                                            ";
                // line 130
                echo twig_escape_filter($this->env, twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 130), "js"), "html", null, true);
                echo ",
                                            ";
                // line 131
                echo twig_escape_filter($this->env, twig_escape_filter($this->env, ($context["groupProjectId"] ?? null), "js"), "html", null, true);
                echo ",
                                            ";
                // line 132
                echo twig_escape_filter($this->env, twig_escape_filter($this->env, ($context["ticketCreationDueDate"] ?? null), "js"), "html", null, true);
                echo "
                                        )\"
                                    ><i class=\"fa fa-plus\"></i> ";
                // line 134
                echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("links.add_todo_no_icon"), "html", null, true);
                echo "</a>
                                </h5>
                                <div id=\"accordion_<?=\$i ?>\" class=\"simpleAccordionContainer\">
                                    <ul class=\"sortableTicketList\">
                                        ";
                // line 138
                if (twig_length_filter($this->env, twig_get_attribute($this->env, $this->source, $context["ticketGroup"], "tickets", [], "any", false, false, false, 138))) {
                    // line 139
                    echo "                                            <em>Nothing to see here. Move on.</em><br /><br />
                                        ";
                }
                // line 141
                echo "
                                        ";
                // line 142
                if ( !twig_test_empty(twig_get_attribute($this->env, $this->source, $context["ticketGroup"], "tickets", [], "any", false, false, false, 142))) {
                    // line 143
                    echo "                                            ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["ticketGroup"], "tickets", [], "any", false, false, false, 143));
                    foreach ($context['_seq'] as $context["_key"] => $context["row"]) {
                        // line 144
                        echo "                                                ";
                        // line 145
                        echo "                                                    ";
                        if (((twig_get_attribute($this->env, $this->source, $context["row"], "dateToFinish", [], "any", false, false, false, 145) == "0000-00-00 00:00:00") || (twig_get_attribute($this->env, $this->source, $context["row"], "dateToFinish", [], "any", false, false, false, 145) == "1969-12-31 00:00:00"))) {
                            // line 146
                            echo "                                                        ";
                            $context["date"] = $this->env->getFunction('__')->getCallable()("text.anytime");
                            // line 147
                            echo "                                                    ";
                        } else {
                            // line 148
                            echo "                                                        ";
                            $context["date"] = twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "dateToFinish", [], "any", false, false, false, 148), $this->env->getFunction('__')->getCallable()("language.dateformat"));
                            // line 149
                            echo "                                                    ";
                        }
                        // line 150
                        echo "                                                ";
                        // line 151
                        echo "                                                <li class=\"ui-state-default\" id=\"ticket_";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 151), "html", null, true);
                        echo "\" >
                                                    <div class=\"ticketBox fixed priority-border-<?=\$row['priority']?>\" data-val=\"";
                        // line 152
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 152), "html", null, true);
                        echo "\">
                                                        <div class=\"row\">
                                                            <div class=\"col-md-12 timerContainer\" style=\"padding:5px 15px;\" id=\"timerContainer-";
                        // line 154
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 154), "html", null, true);
                        echo "\">
                                                                ";
                        // line 155
                        if ((twig_get_attribute($this->env, $this->source, ($context["currentUser"] ?? null), "role", [], "any", false, false, false, 155) > twig_get_attribute($this->env, $this->source, ($context["roles"] ?? null), "editor", [], "any", false, false, false, 155))) {
                            // line 156
                            echo "                                                                    <div class=\"inlineDropDownContainer\">
                                                                        <a href=\"javascript:void(0)\" class=\"dropdown-toggle ticketDropDown\" data-toggle=\"dropdown\">
                                                                            <i class=\"fa fa-ellipsis-v\" aria-hidden=\"true\"></i>
                                                                        </a>
                                                                        <ul class=\"dropdown-menu\">
                                                                            <li class=\"nav-header\">";
                            // line 161
                            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("subtitles.todo"), "html", null, true);
                            echo "</li>
                                                                            <li><a href=\"";
                            // line 162
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "base_url", [], "any", false, false, false, 162), "html", null, true);
                            echo "/tickets/showTicket/";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 162), "html", null, true);
                            echo "\" class='ticketModal'><i class=\"fa fa-edit\"></i> ";
                            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("links.edit_todo"), "html", null, true);
                            echo "</a></li>
                                                                            <li><a href=\"";
                            // line 163
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "base_url", [], "any", false, false, false, 163), "html", null, true);
                            echo "/tickets/delTicket/";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 163), "html", null, true);
                            echo "\" class=\"delete\"><i class=\"fa fa-trash\"></i> ";
                            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("links.delete_todo"), "html", null, true);
                            echo "</a></li>
                                                                            <li class=\"nav-header border\">";
                            // line 164
                            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("subtitles.track_time"), "html", null, true);
                            echo "</li>
                                                                            <li id=\"timerContainer-";
                            // line 165
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 165), "html", null, true);
                            echo "\" class=\"timerContainer\">
                                                                                <a
                                                                                    class=\"punchIn\"
                                                                                    href=\"javascript:void(0);\"
                                                                                    data-value=\"";
                            // line 169
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 169), "html", null, true);
                            echo "\"
                                                                                    ";
                            // line 170
                            if (twig_test_empty(($context["onTheClock"] ?? null))) {
                                // line 171
                                echo "                                                                                        style='display:none'
                                                                                    ";
                            }
                            // line 173
                            echo "                                                                                ><span class=\"fa-regular fa-clock\"></span> ";
                            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("links.start_work"), "html", null, true);
                            echo "</a>
                                                                                <a
                                                                                    class=\"punchOut\"
                                                                                    href=\"javascript:void(0);\"
                                                                                    data-value=\"";
                            // line 177
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 177), "html", null, true);
                            echo "\"
                                                                                    ";
                            // line 178
                            if ((twig_test_empty(($context["onTheClock"] ?? null)) || (twig_get_attribute($this->env, $this->source, ($context["onTheClock"] ?? null), "id", [], "any", false, false, false, 178) != twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 178)))) {
                                // line 179
                                echo "                                                                                        style='display:none'
                                                                                    ";
                            }
                            // line 181
                            echo "                                                                                ><span class=\"fa-stop\"></span>
                                                                                ";
                            // line 182
                            if ( !twig_test_empty(($context["onTheClock"] ?? null))) {
                                // line 183
                                echo "                                                                                    ";
                                echo twig_escape_filter($this->env, $this->env->getFunction('sf')->getCallable()($this->env->getFunction('__')->getCallable()("links.stop_work_started_at"), twig_date_converter($this->env, $this->env->getFunction('__')->getCallable()("language.timeformat")), twig_get_attribute($this->env, $this->source, ($context["onTheClick"] ?? null), "since", [], "any", false, false, false, 183)), "html", null, true);
                                echo "
                                                                                ";
                            } else {
                                // line 185
                                echo "                                                                                    ";
                                echo twig_escape_filter($this->env, $this->env->getFunction('sf')->getCallable()($this->env->getFunction('__')->getCallable()("links.stop_work_started_at"), twig_date_converter($this->env, $this->env->getFunction('__')->getCallable()("language.timeformat")), $this->env->getFunction('time')->getCallable()()), "html", null, true);
                                echo "
                                                                                ";
                            }
                            // line 187
                            echo "                                                                                </a>
                                                                                <span
                                                                                    class='working'
                                                                                    ";
                            // line 190
                            if ((twig_test_empty(($context["onTheClock"] ?? null)) || (twig_get_attribute($this->env, $this->source, ($context["onTheClock"] ?? null), "id", [], "any", false, false, false, 190) == twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 190)))) {
                                // line 191
                                echo "                                                                                        style='display:none;'
                                                                                    ";
                            }
                            // line 193
                            echo "                                                                                >";
                            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("text.timer_set_other_todo"), "html", null, true);
                            echo "</span>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                ";
                        }
                        // line 198
                        echo "                                                                <small>";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "projectName", [], "any", false, false, false, 198));
                        echo "</small><br />
                                                                <strong><a class='ticketModal' href=\"";
                        // line 199
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "base_url", [], "any", false, false, false, 199), "html", null, true);
                        echo "/tickets/showTicket/";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 199), "html", null, true);
                        echo "\" >";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "headline", [], "any", false, false, false, 199));
                        echo "</a></strong>
                                                            </div>
                                                        </div>
                                                        <div class=\"row\">
                                                            <div class=\"col-md-4\" style=\"padding:0 15px;\">
                                                                ";
                        // line 204
                        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("label.due"), "html", null, true);
                        echo "<input type=\"text\" title=\"";
                        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("label.due"), "html", null, true);
                        echo "\" value=\"";
                        echo twig_escape_filter($this->env, ($context["date"] ?? null), "html", null, true);
                        echo "\" class=\"duedates secretInput\" data-id=\"";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 204), "html", null, true);
                        echo "\" name=\"date\" />
                                                            </div>
                                                            <div class=\"col-md-8\" style=\"padding-top:3px;\" >
                                                                <div class=\"right\">
                                                                    <div class=\"dropdown ticketDropdown effortDropdown show\">
                                                                        <a class=\"dropdown-toggle f-left  label-default effort\" href=\"javascript:void(0);\" role=\"button\" id=\"effortDropdownMenuLink";
                        // line 209
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 209), "html", null, true);
                        echo "\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                                                                        <span class=\"text\">
                                                                            ";
                        // line 211
                        echo twig_escape_filter($this->env, ((( !twig_test_empty(twig_get_attribute($this->env, $this->source, $context["row"], "storypoints", [], "any", false, false, false, 211)) && (twig_get_attribute($this->env, $this->source, $context["row"], "storypoints", [], "any", false, false, false, 211) > 0))) ? ((($__internal_compile_4 = ($context["efforts"] ?? null)) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4[twig_get_attribute($this->env, $this->source, $context["row"], "storypoints", [], "any", false, false, false, 211)] ?? null) : null)) : ($this->env->getFunction('__')->getCallable()("label.story_points_unkown"))), "html", null, true);
                        echo "
                                                                        </span>
                                                                            &nbsp;<i class=\"fa fa-caret-down\" aria-hidden=\"true\"></i>
                                                                        </a>
                                                                        <ul class=\"dropdown-menu\" aria-labelledby=\"effortDropdownMenuLink";
                        // line 215
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 215), "html", null, true);
                        echo "\">
                                                                            <li class=\"nav-header border\">";
                        // line 216
                        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("dropdown.how_big_todo"), "html", null, true);
                        echo "</li>
                                                                            ";
                        // line 217
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable(($context["efforts"] ?? null));
                        foreach ($context['_seq'] as $context["effortKey"] => $context["effortValue"]) {
                            // line 218
                            echo "                                                                                <li class='dropdown-item'>
                                                                                    <a href='javascript:void(0);' data-value='";
                            // line 219
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 219), "html", null, true);
                            echo "_";
                            echo twig_escape_filter($this->env, $context["effortKey"], "html", null, true);
                            echo "' id='ticketEffortChange";
                            echo twig_escape_filter($this->env, (twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 219) . $context["effortKey"]), "html", null, true);
                            echo "'>";
                            echo twig_escape_filter($this->env, $context["effortValue"], "html", null, true);
                            echo "</a>
                                                                                </li>
                                                                            ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['effortKey'], $context['effortValue'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 222
                        echo "                                                                        </ul>
                                                                    </div>
                                                                    <div class=\"dropdown ticketDropdown milestoneDropdown colorized show\">
                                                                        <a style=\"background-color:";
                        // line 225
                        echo twig_escape_filter($this->env, twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "milestoneColor", [], "any", false, false, false, 225), "css"), "html", null, true);
                        echo "\" class=\"dropdown-toggle f-left  label-default milestone\" href=\"javascript:void(0);\" role=\"button\" id=\"milestoneDropdownMenuLink";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 225), "html", null, true);
                        echo "\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                                                                        <span class=\"text\">
                                                                            ";
                        // line 227
                        ((( !twig_test_empty(twig_get_attribute($this->env, $this->source, $context["row"], "dependingTicketId", [], "any", false, false, false, 227)) && (twig_get_attribute($this->env, $this->source, $context["row"], "dependingTicketId", [], "any", false, false, false, 227) != 0))) ? (print (twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "milestoneHeadline", [], "any", false, false, false, 227)))) : (print (twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("label.no_milestone"), "html", null, true))));
                        echo "
                                                                        </span>
                                                                            &nbsp;<i class=\"fa fa-caret-down\" aria-hidden=\"true\"></i>
                                                                        </a>
                                                                        <ul class=\"dropdown-menu\" aria-labelledby=\"milestoneDropdownMenuLink";
                        // line 231
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 231), "html", null, true);
                        echo "\">
                                                                            <li class=\"nav-header border\">";
                        // line 232
                        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("dropdown.choose_milestone"), "html", null, true);
                        echo "</li>
                                                                            <li class='dropdown-item'><a style='background-color:#1b75bb' href='javascript:void(0);' data-label=\"";
                        // line 233
                        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("label.no_milestone"), "html", null, true);
                        echo "\" data-value='";
                        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 233), "html", null, true);
                        echo "_0_#1b75bb'> ";
                        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("label.no_milestone"), "html", null, true);
                        echo " </a></li>
                                                                            ";
                        // line 234
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable((($__internal_compile_5 = ($context["milestones"] ?? null)) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5[twig_get_attribute($this->env, $this->source, $context["row"], "projectId", [], "any", false, false, false, 234)] ?? null) : null));
                        foreach ($context['_seq'] as $context["_key"] => $context["milestone"]) {
                            // line 235
                            echo "                                                                                <li class='dropdown-item'>
                                                                                    <a href='javascript:void(0);' data-label='";
                            // line 236
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["milestone"], "headline", [], "any", false, false, false, 236));
                            echo "' data-value='";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 236), "html", null, true);
                            echo "_";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["milestone"], "id", [], "any", false, false, false, 236), "html", null, true);
                            echo "_";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["milestone"], "tags", [], "any", false, false, false, 236));
                            echo "' id='ticketMilestoneChange";
                            echo twig_escape_filter($this->env, (twig_get_attribute($this->env, $this->source, $context["row"], "id", [], "any", false, false, false, 236) . twig_get_attribute($this->env, $this->source, $context["milestone"], "id", [], "any", false, false, false, 236)), "html", null, true);
                            echo "' style='background-color:";
                            echo twig_escape_filter($this->env, twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["milestone"], "tags", [], "any", false, false, false, 236), "css"), "html", null, true);
                            echo "'>";
                            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["milestone"], "headline", [], "any", false, false, false, 236));
                            echo "</a>
                                                                                </li>
                                                                            ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['milestone'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 239
                        echo "                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </li>
                                            ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['row'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 248
                    echo "                                        ";
                }
                // line 249
                echo "                                    </ul>
                                </div>
                            ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ticketGroup'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 252
            echo "                        ";
        }
        // line 253
        echo "                    </div>
                </div>
            </div>
        </div> <!-- End .col-md-8 -->
        <div class=\"col-md-4\">
            <div class=\"maincontentinner\">
                <a href=\"";
        // line 259
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "base_url", [], "any", false, false, false, 259), "html", null, true);
        echo "/projects/showMy\" class=\"pull-right\">";
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("links.my_portfolio"), "html", null, true);
        echo "</a>
                <h5 class=\"subtitle\">";
        // line 260
        echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("headline.your_projects"), "html", null, true);
        echo "</h5>
                <br/>
                ";
        // line 262
        if ((twig_length_filter($this->env, ($context["allProjects"] ?? null)) == 0)) {
            // line 263
            echo "                    <div class='col-md-12'>
                        <br /><br />
                        <div class='center'>
                            <div style='width:70%' class='svgContainer'>
                                ";
            // line 267
            echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("notifications.not_assigned_to_any_project"), "html", null, true);
            echo "
                                ";
            // line 268
            if ((twig_get_attribute($this->env, $this->source, ($context["user"] ?? null), "role", [], "any", false, false, false, 268) > twig_get_attribute($this->env, $this->source, ($context["roles"] ?? null), "manager", [], "any", false, false, false, 268))) {
                // line 269
                echo "                                    <br /><br />
                                    <a href='";
                // line 270
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "base_url", [], "any", false, false, false, 270), "html", null, true);
                echo "/projects/newProject' class='btn btn-primary'>";
                echo twig_escape_filter($this->env, $this->env->getFunction('__')->getCallable()("link.new_project"), "html", null, true);
                echo "</a>
                                ";
            }
            // line 272
            echo "                            </div>
                        </div>
                    </div>
                ";
        }
        // line 276
        echo "                <ul class=\"sortableTicketList\" id=\"projectProgressContainer\">
                    ";
        // line 277
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["allProjects"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["project"]) {
            // line 278
            echo "                        ";
            $context["percentDone"] = twig_round(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["project"], "progress", [], "any", false, false, false, 278), "percent", [], "any", false, false, false, 278));
            // line 279
            echo "                        <li>
                            <div class=\"col-md-12\">
                                <div class=\"row\" >
                                    <div class=\"col-md-12 ticketBox fixed\">
                                        <div class=\"row\" style=\"padding-bottom:10px;\">
                                            <div class=\"col-md-8\">
                                                <a href=\"";
            // line 285
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "base_url", [], "any", false, false, false, 285), "html", null, true);
            echo "/dashboard/show?projectId=";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["project"], "id", [], "any", false, false, false, 285), "html", null, true);
            echo "\">
                                                    ";
            // line 286
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["project"], "clientName", [], "any", false, false, false, 286));
            echo " \\\\
                                                    ";
            // line 287
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["project"], "name", [], "any", false, false, false, 287));
            echo "
                                                </a>
                                            </div>
                                            <div class=\"col-md-4\" style=\"text-align:right\">
                                                ";
            // line 291
            echo twig_escape_filter($this->env, $this->env->getFunction('sf')->getCallable()($this->env->getFunction('__')->getCallable()("text.percent_complete"), ($context["percentDone"] ?? null)), "html", null, true);
            echo "
                                            </div>
                                        </div>
                                        <div class=\"progress\">
                                            <div class=\"progress-bar progress-bar-success\" role=\"progressbar\" aria-valuenow=\"";
            // line 295
            echo twig_escape_filter($this->env, ($context["percentDone"] ?? null), "html", null, true);
            echo "\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: ";
            echo twig_escape_filter($this->env, twig_escape_filter($this->env, ($context["percentDone"] ?? null), "css"), "html", null, true);
            echo "%\">
                                                <span class=\"sr-only\">";
            // line 296
            echo twig_escape_filter($this->env, $this->env->getFunction('sf')->getCallable()($this->env->getFunction('__')->getCallable()("text.percent_complete"), ($context["percentDone"] ?? null)), "html", null, true);
            echo "</span>
                                            </div>
                                        </div>
                                        <div class=\"row\">
                                            <div class=\"col-md-12\"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['project'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 307
        echo "                </ul>
            </div>
        </div> <!-- End .col-md-4 -->
    </div> <!-- End .row -->
";
    }

    // line 313
    public function block_scripts($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 314
        echo "    <script type=\"text/javascript\">

        ";
        // line 316
        echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->env->getFunction('dispatchEvent')->getCallable()("scripts.afterOpen"), "js"), "html", null, true);
        echo "

        function insertQuickAddForm(index, projectId, duedate) {
            jQuery(\".quickaddForm\").remove();

            jQuery(\"#accordion_\"+index+\" ul\").prepend('<li class=\"quickaddForm\">'+
                ' <div class=\"ticketBox\" id=\"ticket_new_'+index+'\" style=\"padding:18px;\">'+
                '<form method=\"post\" class=\"form-group\" action=\"#accordion_anchor_'+index+'\">'+
                '<input name=\"headline\" type=\"text\" title=\"<?php echo \$this->__(\"label.headline\"); ?>\" style=\"width:100%\" placeholder=\"<?php echo \$this->__(\"input.placeholders.what_are_you_working_on\"); ?>\" />'+
                '<input type=\"submit\" value=\"<?php echo \$this->__(\"buttons.save\"); ?>\" name=\"quickadd\"  />'+
                '<input type=\"hidden\" name=\"dateToFinish\" id=\"dateToFinish\" value=\"'+duedate+'\" />'+
                '<input type=\"hidden\" name=\"status\" value=\"3\" />'+
                '<input type=\"hidden\" name=\"projectId\" value=\"'+projectId+'\" />'+
                '<input type=\"hidden\" name=\"sprint\" value=\"";
        // line 329
        echo twig_escape_filter($this->env, twig_escape_filter($this->env, ($context["currentSprint"] ?? null), "js"), "html", null, true);
        echo "\" />&nbsp;'+
                '<a href=\"javascript:void(0);\" onclick=\"jQuery(\\'#ticket_new_'+index+'\\').toggle(\\'fast\\');\">'+
            '<?php echo \$this->__(\"links.cancel\"); ?>'+
            '</a>'+
            '</form></div></li>');
        }

        function accordionToggle(id) {

            let currentLink = jQuery(\"#accordion_toggle_\"+id).find(\"i.fa\");

                if(currentLink.hasClass(\"fa-angle-right\")){
                    currentLink.removeClass(\"fa-angle-right\");
                    currentLink.addClass(\"fa-angle-down\");
                    jQuery('#accordion_'+id).slideDown(\"fast\");
                }else{
                    currentLink.removeClass(\"fa-angle-down\");
                    currentLink.addClass(\"fa-angle-right\");
                    jQuery('#accordion_'+id).slideUp(\"fast\");
                }

        }

    jQuery(document).ready(function() {
        jQuery('.todaysDate').text(moment().format('LLLL'));

            ";
        // line 355
        if ((twig_get_attribute($this->env, $this->source, ($context["user"] ?? null), "role", [], "any", false, false, false, 355) >= twig_get_attribute($this->env, $this->source, ($context["roles"] ?? null), "editor", [], "any", false, false, false, 355))) {
            // line 356
            echo "                leantime.dashboardController.prepareHiddenDueDate();
                leantime.ticketsController.initEffortDropdown();
                leantime.ticketsController.initMilestoneDropdown();
                leantime.ticketsController.initStatusDropdown();
                leantime.ticketsController.initDueDateTimePickers();
            ";
        } else {
            // line 362
            echo "                leantime.generalController.makeInputReadonly(\".maincontentinner\");
            ";
        }
        // line 364
        echo "
            ";
        // line 365
        if ((twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["user"] ?? null), "settings", [], "any", false, false, false, 365), "modal", [], "any", false, false, false, 365), "dashboard", [], "any", false, false, false, 365) != true)) {
            // line 366
            echo "
                leantime.helperController.showHelperModal(\"dashboard\", 500, 700);

                ";
            // line 374
            echo "
            ";
        }
        // line 376
        echo "
        });

        ";
        // line 379
        echo twig_escape_filter($this->env, twig_escape_filter($this->env, $this->env->getFunction('dispatchEvent')->getCallable()("scripts.beforeClose"), "js"), "html", null, true);
        echo "

    </script>
";
    }

    public function getTemplateName()
    {
        return "home.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  877 => 379,  872 => 376,  868 => 374,  863 => 366,  861 => 365,  858 => 364,  854 => 362,  846 => 356,  844 => 355,  815 => 329,  799 => 316,  795 => 314,  791 => 313,  783 => 307,  766 => 296,  760 => 295,  753 => 291,  746 => 287,  742 => 286,  736 => 285,  728 => 279,  725 => 278,  721 => 277,  718 => 276,  712 => 272,  705 => 270,  702 => 269,  700 => 268,  696 => 267,  690 => 263,  688 => 262,  683 => 260,  677 => 259,  669 => 253,  666 => 252,  650 => 249,  647 => 248,  633 => 239,  612 => 236,  609 => 235,  605 => 234,  597 => 233,  593 => 232,  589 => 231,  582 => 227,  575 => 225,  570 => 222,  555 => 219,  552 => 218,  548 => 217,  544 => 216,  540 => 215,  533 => 211,  528 => 209,  514 => 204,  502 => 199,  497 => 198,  488 => 193,  484 => 191,  482 => 190,  477 => 187,  471 => 185,  465 => 183,  463 => 182,  460 => 181,  456 => 179,  454 => 178,  450 => 177,  442 => 173,  438 => 171,  436 => 170,  432 => 169,  425 => 165,  421 => 164,  413 => 163,  405 => 162,  401 => 161,  394 => 156,  392 => 155,  388 => 154,  383 => 152,  378 => 151,  376 => 150,  373 => 149,  370 => 148,  367 => 147,  364 => 146,  361 => 145,  359 => 144,  354 => 143,  352 => 142,  349 => 141,  345 => 139,  343 => 138,  336 => 134,  331 => 132,  327 => 131,  323 => 130,  314 => 124,  310 => 123,  306 => 122,  302 => 121,  297 => 120,  295 => 119,  292 => 118,  289 => 117,  286 => 116,  283 => 115,  280 => 114,  278 => 113,  260 => 112,  258 => 111,  255 => 110,  246 => 106,  242 => 105,  238 => 104,  232 => 101,  228 => 99,  226 => 98,  217 => 91,  208 => 89,  204 => 87,  202 => 86,  198 => 85,  195 => 84,  191 => 83,  187 => 82,  182 => 80,  173 => 74,  167 => 73,  159 => 68,  153 => 67,  146 => 63,  139 => 59,  133 => 55,  113 => 37,  111 => 36,  95 => 25,  83 => 17,  79 => 16,  73 => 13,  68 => 11,  63 => 9,  57 => 6,  51 => 4,  47 => 3,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "home.twig", "/Users/josephroberts/localdev/leantime/src/domain/dashboard/templates/home.twig");
    }
}
