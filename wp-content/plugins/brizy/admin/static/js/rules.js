var h = hyperapp.h;

var RULE_TYPE_INCLUDE = "1";
var RULE_TYPE_EXCLUDE = "2";

var RULE_POSTS = "1";
var RULE_TAXONOMY = "2";
var RULE_ARCHIVE = "4";
var RULE_TEMPLATE = "8";
var RULE_BRIZY_TEMPLATE = "16";
var POSTS_FROM_TAXONOMY = "32";
var POSTS_FROM_CHILD_TAXONOMY = "64";
var ANY_CHILD_TAXONOMY = "128";

var defaultTemplateType = Brizy_Admin_Rules.templateType !== '' ? Brizy_Admin_Rules.templateType : 'single';
var defaultAppliedFor = null;
var defaultEntityType = null;

switch (defaultTemplateType) {
    case 'archive':
        defaultAppliedFor = RULE_TAXONOMY;
        defaultEntityType = 'category';
        break;

    default:
        defaultAppliedFor = RULE_POSTS;
        defaultEntityType = 'post';
        break;
}


var defaultRule = {
    type: RULE_TYPE_INCLUDE,
    appliedFor: defaultAppliedFor,
    entityType: defaultEntityType,
    entityValues: []
};

var state = {
    templateType: defaultTemplateType,
    rule: defaultRule,
    rules: {
        single: [],
        archive: [],
        single_product: [],
        product_archive: [],
    },
    errors: "",
    groups: [],
};

state.rules[state.templateType] = Brizy_Admin_Rules.rules;

var apiCache = {
    groupList: null,
    postGroupListPromise: [],
    archiveGroupListPromise: [],
    postList: [],
    termList: []
};

var api = {
    getGroupList: function (templateType) {

        if (!apiCache.groupList) {
            apiCache.groupList = [];
        }

        if (apiCache.groupList && apiCache.groupList[templateType])
            return apiCache.groupList[templateType];

        return apiCache.groupList[templateType] = jQuery.getJSON(Brizy_Admin_Rules.url, {
            action: "brizy_rule_group_list",
            hash: Brizy_Admin_Rules.hash,
            version: Brizy_Admin_Data.editorVersion,
            context: 'template-rules',
            templateType: templateType
        })
    },

    getPostsGroupList: function (postType, templateType) {

        if (apiCache.postGroupListPromise[postType + templateType])
            return apiCache.postGroupListPromise[postType + templateType];

        return apiCache.postGroupListPromise[postType + templateType] = jQuery.getJSON(Brizy_Admin_Rules.url, {
            action: "brizy_rule_posts_group_list",
            postType: postType,
            hash: Brizy_Admin_Rules.hash,
            version: Brizy_Admin_Data.editorVersion,
            context: 'template-rules',
            templateType: templateType
        })
    },

    getArchiveGroupList: function (taxonomy, templateType) {

        if (apiCache.archiveGroupListPromise[taxonomy + templateType])
            return apiCache.archiveGroupListPromise[taxonomy + templateType];

        return apiCache.archiveGroupListPromise[taxonomy + templateType] = jQuery.getJSON(Brizy_Admin_Rules.url, {
            action: "brizy_rule_archive_group_list",
            taxonomy: taxonomy,
            hash: Brizy_Admin_Rules.hash,
            version: Brizy_Admin_Data.editorVersion,
            context: 'template-rules',
            templateType: templateType
        })
    },


    getTerms: function (taxonomy) {
        if (apiCache.termList[taxonomy])
            return apiCache.termList[taxonomy];

        return jQuery.getJSON(Brizy_Admin_Rules.url, {
            action: "brizy_get_terms",
            hash: Brizy_Admin_Rules.hash,
            version: Brizy_Admin_Data.editorVersion,
            taxonomy: taxonomy
        }).done(function (data) {
            apiCache.termList[taxonomy] = jQuery.Deferred().resolve(data);
        });
    },

    validateRule: function (rule) {

        var url = new URL(Brizy_Admin_Rules.url);
        url.searchParams.append('action', 'brizy_validate_rule');
        url.searchParams.append('hash', Brizy_Admin_Rules.hash);
        url.searchParams.append('post', Brizy_Admin_Rules.id);
        url.searchParams.append('version', Brizy_Admin_Data.editorVersion);
        url.searchParams.append('dataVersion', 0);
        url.searchParams.append('ignoreDataVersion', 1);

        return jQuery.ajax({
            type: "POST",
            url: url.toString(),
            data: JSON.stringify(rule),
            contentType: "application/json; charset=utf-8"
        });
    },
};

var actions = {
    getState: function (value) {
        return function (state) {
            return state;
        };
    },

    updateGroups: function (value) {
        return function (state) {
            return {groups: value};
        };
    },

    rule: {
        update: function (rule) {
            return function (state) {
                return rule;
            };
        },

        setType: function (value) {
            return function (state) {
                return {type: value};
            };
        },
        setAppliedFor: function (value) {
            return function (state) {
                return {appliedFor: value};
            };
        },
        setEntityType: function (value) {
            return function (state) {
                return {entityType: value};
            };
        },
        setEntityValues: function (value) {
            return function (state) {
                return {entityValues: value};
            };
        }
    },

    resetRule: function () {
        return function (state) {

            switch (state.templateType) {
                case 'archive':
                    defaultAppliedFor = RULE_TAXONOMY;
                    defaultEntityType = 'category';
                    break;

                default:
                    defaultAppliedFor = RULE_POSTS;
                    defaultEntityType = 'post';
                    break;
            }

            var defaultRule = {
                type: RULE_TYPE_INCLUDE,
                appliedFor: defaultAppliedFor,
                entityType: defaultEntityType,
                entityValues: []
            };


            return {errors: "", rule: defaultRule};
        };
    },
    addFormErrors: function (errors) {
        return function (state) {
            return {errors: errors};
        };
    },

    addRule: function (rule) {
        return function (state) {
            return {
                rules: {[state.templateType]: [...state.rules[state.templateType], rule]}
            };
        };
    },
    removeRule: function (rule) {
        return function (state) {
            return {
                rules: {
                    [state.templateType]: state.rules[state.templateType].filter(function (arule) {
                        return arule != rule;
                    })
                }
            };
        };
    },
    setTemplateType: function (type) {
        return function (state) {
            return {
                templateType: type,
                // rules: {
                //     single: [],
                //     archive: [],
                //     single_product: [],
                //     product_archive: [],
                // }
            };
        };
    }
};

function arr_diff(a1, a2) {

    var a = [], diff = [];

    for (var i = 0; i < a1.length; i++) {
        a[a1[i]] = true;
    }

    for (var i = 0; i < a2.length; i++) {
        if (a[a2[i]]) {
            delete a[a2[i]];
        } else {
            a[a2[i]] = true;
        }
    }

    for (var k in a) {
        diff.push(k);
    }

    return diff;
}


var RuleTypeField = function (params) {
    return function (state, action) {
        return h(
            "span",
            {
                class: "brizy-rule-select",
            },

            h(
                "select",
                {
                    name: params.name,
                    style: {width: "100px"},
                    oncreate: function (element) {
                        var el = jQuery(element);
                        el.on("change", params.onChange);
                        el.select2();
                    },
                    onremove: function (element, done) {
                        jQuery(element).select2("destroy");
                        done();
                    },
                    onchange: function (e) {
                        action.rule.setType(e.target.value);
                    }
                },
                [
                    h(
                        "option",
                        {
                            value: RULE_TYPE_INCLUDE,
                            selected: params.value === RULE_TYPE_INCLUDE
                        },
                        "Include"
                    ),
                    h(
                        "option",
                        {
                            value: RULE_TYPE_EXCLUDE,
                            selected: params.value === RULE_TYPE_EXCLUDE
                        },
                        "Exclude"
                    )
                ]
            )
        );
    };
};

var BrzSelect2 = function (params) {

    var oncreate = function (element) {

        var el = jQuery(element);
        el.on("change", params.onChange);
        el.select2();

        if (typeof params.optionRequest === 'function') {
            const optionRequest = params.optionRequest();

            if (typeof params.optionRequest === 'function') {
                optionRequest.done(function (response) {
                    var options = params.convertResponseToOptions(response);
                    options.forEach(function (option) {
                        el.append(option);
                    });
                    el.trigger("change");
                });
            } else {
                var options = params.convertResponseToOptions(optionRequest);
                options.forEach(function (option) {
                    el.append(option);
                });

                el.trigger("change");
            }
        }
    };

    var onremove = function (element, done) {
        jQuery(element).select2("destroy");
        done();
    };

    return h(
        "select",
        {
            key: params.id + '|' + params.value,
            akey: params.id + '|' + params.value,
            style: params.style,
            oncreate: oncreate,
            onremove: onremove,
            onchange: params.onChange,
            name: params.name,
        },
        []
    );
};

var RulePostsGroupSelectField = function (params) {

    var appliedFor = params.rule.appliedFor;
    var entityType = params.rule.entityType;
    var value = String(params.rule.entityValues[0] ? params.rule.entityValues[0] : '');

    var convertResponseToOptions = function (response) {
        var groups = [];
        groups.push(new Option("All", '', false, value === ''));
        response.data.forEach(function (group) {

            if (group.title === "") {
                group.items.forEach(function (option) {
                    var optionValue = String(option.value);
                    groups.push(new Option(option.title, optionValue, false, params.rule.entityValues.includes(optionValue)));
                });
            } else {
                var groupElement = document.createElement("OPTGROUP");
                groupElement.label = group.title;

                if (group.items.length > 0) {
                    group.items.forEach(function (option) {
                        var optionValue = String(option.value);
                        groupElement.appendChild(new Option(option.title, optionValue, false, params.rule.entityValues.includes(optionValue)))
                    });
                    groups.push(groupElement);
                }
            }
        });

        return groups;
    };


    return h(
        BrzSelect2,
        {
            id: "post-groups-" + entityType,
            style: params.style ? params.style : {width: "200px"},
            name: params.name,
            optionRequest: function () {
                return api.getPostsGroupList(entityType, params.type);
            },
            convertResponseToOptions: convertResponseToOptions,
            onChange: params.onChange,
        },
        []
    );
};

var RuleArchiveGroupSelectField = function (params) {

    var appliedFor = params.rule.appliedFor;
    var taxonomy = params.rule.entityType;
    var value = String(params.rule.entityValues[0] ? params.rule.entityValues[0] : '');

    var convertResponseToOptions = function (response) {
        var groups = [];
        groups.push(new Option("All", '', false, value === ''));
        response.data.forEach(function (group) {

            if (group.title === "") {
                group.items.forEach(function (option) {
                    var optionValue = String(option.value);
                    groups.push(new Option(option.title, optionValue, false, params.rule.entityValues.includes(optionValue)));
                });
            } else {
                var groupElement = document.createElement("OPTGROUP");
                groupElement.label = group.title;

                if (group.items.length > 0) {
                    group.items.forEach(function (option) {
                        var optionValue = String(option.value);
                        groupElement.appendChild(new Option(option.title, optionValue, false, params.rule.entityValues.includes(optionValue)))
                    });
                    groups.push(groupElement);
                }
            }
        });

        return groups;
    };

    return h(
        BrzSelect2,
        {
            id: "archive-groups-" + params.taxonomy,
            style: params.style ? params.style : {width: "200px"},
            name: params.name,
            optionRequest: function () {
                return api.getArchiveGroupList(taxonomy, params.type);
            },
            convertResponseToOptions: convertResponseToOptions,
            onChange: params.onChange,
        },
        []
    );
};

var RuleApplyGroupField = function (params) {
    return function (state, actions) {

        var appliedFor = params.rule.appliedFor;
        var entityType = params.rule.entityType;
        var value = appliedFor + "|" + entityType;
        var groups = [];

        params.groups.forEach(function (group) {
            var options = [];
            group.items.forEach(function (option) {
                var optionValue = option.groupValue + "|" + option.value;
                var attributes = {
                    value: optionValue,
                    selected: optionValue === value
                };
                options.push(h("option", attributes, option.title));
            });
            const attributes = {label: group.title};

            if (group.value + "|" === "|") {
                groups.push(h("option", {value: "|"}, group.title));
            } else {
                groups.push(h("optgroup", attributes, options));
            }
        });

        const attributes = {
            name: params.name,
            style: {width: "200px"},
            class: "brizy-rule-select--options[] ",
            onchange: function (e) {
                var values = e.target.value.split("|");
                actions.rule.update({
                    appliedFor: values[0],
                    entityType: values[1],
                    entityValues: []
                });
            }
        };

        var elements = [
            h("span", {class: "brizy-rule-select brizy-rule-select2"},
                h("select", attributes, groups)
            )
        ];

        switch (appliedFor) {
            case RULE_POSTS:
                elements.push(
                    h("span", {class: "brizy-rule-select brizy-rule-select2"}, [
                        h(RulePostsGroupSelectField, {
                            id: appliedFor + value,
                            rule: params.rule,
                            type: params.type,
                            name: params.type ? 'brizy-' + params.type + '-rule-entity-values[]' : '',
                            onChange: function (e) {
                                if (e.target.value)
                                    actions.rule.update({
                                        entityValues: [e.target.value],
                                    });
                            }
                        })
                    ]));
                break;
            case RULE_TAXONOMY:
                elements.push(
                    h("span", {class: "brizy-rule-select brizy-rule-select2"}, [
                        h(RuleArchiveGroupSelectField, {
                            id: appliedFor + value,
                            rule: params.rule,
                            type: params.type,
                            taxonomy: entityType,
                            name: params.type ? 'brizy-' + params.type + '-rule-entity-values[]' : '',
                            onChange: function (e) {
                                if (e.target.value)
                                    actions.rule.update({
                                        entityValues: [e.target.value],
                                    });
                            }
                        })
                    ]));
                break;
        }
        return h("span", {}, elements);
    };
};

var RuleForm = function (params) {
    var elements = [
        h("h4", {}, "Add New Condition"),
        h(RuleTypeField, {value: String(params.rule.type)}),
        h(RuleApplyGroupField, {rule: params.rule, groups: params.groups, type: params.type}),
        h("input", {
            type: "button",
            class: "button",
            onclick: params.onSubmit,
            value: "Add"
        })
    ];

    if (params.errors) {
        elements.push(h("p", {class: "error"}, params.errors));
    }

    return h("div", {class: "brizy-rule-new-condition"}, elements);
};

var RuleListItem = function (params) {
    return h("div", {class: "rule", key: params.index}, [
        h("span", {class: 'rule-fields'}, [
            h(RuleTypeField, {value: String(params.rule.type), name: 'brizy-' + params.type + '-rule-type[]'}),
            h(RuleApplyGroupField, {
                rule: params.rule,
                groups: params.groups,
                type: params.type,
                name: 'brizy-' + params.type + '-rule-group[]'
            }),
            h('div', {class: 'overlay'}, [])
        ]),
        h("input", {
            class: "brizy-delete-rule ",
            type: "button",
            value: "Delete",
            onclick: function (e) {
                if (confirm('Are you sure you want to delete?'))
                    params.onDelete(params.rule);
            }
        })
    ]);
};

var RuleList = function (params) {
    var elements = [];
    if (params.rules.length) {
        elements.push(h("h4", {}, "Where do You Want to Display it"));
    }
    params.rules.forEach(function (rule, index) {
        elements.push(
            h(RuleListItem, {
                index: index,
                rule: rule,
                groups: params.groups,
                onDelete: params.onDelete,
                type: params.type
            })
        );
    });

    return h("div", {}, elements);
};

var TemplateTypeSelect = function (params) {
    return h(
        "fieldset", {class: 'brizy-template-type'}, [
            h('h4', {}, 'Template Type'),
            h('label', {}, [
                h('input', {
                    type: 'radio',
                    name: 'brizy-template-type',
                    onchange: params.onChange,
                    value: 'single',
                    checked: 'single' === params.value
                }),
                h('span', {class: "date-time-text format-i18n"}, Brizy_Admin_Rules.labels.single),
            ]),
            h('label', {}, [
                h('input', {
                    type: 'radio',
                    name: 'brizy-template-type',
                    onchange: params.onChange,
                    value: 'archive',
                    checked: 'archive' === params.value
                }),
                h('span', {class: "date-time-text format-i18n"}, Brizy_Admin_Rules.labels.archive),
            ]),
            ...(Brizy_Admin_Rules.labels.single_product ?
            [h('label', {}, [
                    h('input', {
                        type: 'radio',
                        name: 'brizy-template-type',
                        onchange: params.onChange,
                        value: 'single_product',
                        checked: 'single_product' === params.value
                    }),
                    h('span', {class: "date-time-text format-i18n"}, Brizy_Admin_Rules.labels.single_product),
                ]), h('label', {}, [
                    h('input', {
                        type: 'radio',
                        name: 'brizy-template-type',
                        onchange: params.onChange,
                        value: 'product_archive',
                        checked: 'product_archive' === params.value
                    }),
                    h('span', {class: "date-time-text format-i18n"}, Brizy_Admin_Rules.labels.product_archive),
                ])] : []),
        ]
    );
};

var ruleView = function (state, actions) {

    return h(
        "div",
        {
            oncreate: function () {
                if (state.templateType != '')
                    api.getGroupList(state.templateType).done(function (response) {
                        actions.updateGroups(response.data)
                    });
            },
        },
        [
            h(
                TemplateTypeSelect, {
                    value: state.templateType,
                    onChange: function (e) {
                        const type = e.target.value && e.target.value != "null"
                            ? e.target.value
                            : null;

                        actions.setTemplateType(type);
                        api.getGroupList(type).done(function (response) {
                            actions.updateGroups(response.data);
                            actions.rule.setAppliedFor(String(response.data[0].value));
                            actions.rule.setEntityType(String(response.data[0].items[0].value));
                        });
                    }
                }, []
            ),
            h(RuleList, {
                type: state.templateType,
                rules: state.rules[state.templateType] || [],
                groups: state.groups,
                onDelete: function (rule) {
                    actions.removeRule(rule);
                }
            }),
            state.templateType ? [
                    h(RuleForm, {
                        type: state.templateType,
                        rule: state.rule,
                        groups: state.groups,
                        errors: state.errors,
                        onSubmit: function () {
                            var rules = state.rules[state.templateType] || [];

                            try {
                                rules.forEach(rule => {
                                    if (rule.type !== state.rule.type) return;
                                    if (rule.appliedFor !== state.rule.appliedFor) return;
                                    if (rule.entityType !== state.rule.entityType) return;
                                    if (arr_diff(rule.entityValues, state.rule.entityValues).length > 0) return;
                                    throw 'This rule already exist';

                                });
                            } catch (error) {
                                actions.addFormErrors('This rule already exist');
                                return;
                            }

                            api
                                .validateRule(state.rule)
                                .done(function () {
                                    actions.addRule(state.rule);
                                    actions.resetRule();
                                })
                                .fail(function (response) {
                                    if (response.responseJSON && response.responseJSON.data) {
                                        if (response.responseJSON.data.message)
                                            actions.addFormErrors(response.responseJSON.data.message);
                                        else
                                            actions.addFormErrors("Failed to add the rule");
                                    }
                                });
                        }
                    })] :
                [],
        ]
    );
};

jQuery(document).ready(function ($) {
    hyperapp.app(state, actions, ruleView, document.getElementById("rules"));
});

