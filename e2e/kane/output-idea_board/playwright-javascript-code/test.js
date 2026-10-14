import testmu, { resolveVar, setVar } from '@testmuai/playwright-bindings';
import { expect } from '@playwright/test';

testmu.configure({
  "build": "6d0a1924-52fd-4ae9-8cd4-3305dfb071aa",
  "name": "Add New Idea to Idea Board",
  "tcId": "TC-178",
  "network": (process.env.NETWORK ?? "false").toLowerCase() === "true",
  "autoHealVersion": "AH2",
  "defaultActionTimeoutMs": 10000,
  "defaultNavigationTimeoutMs": 60000,
  "kaneRunV4": true
});

testmu.test("Add New Idea to Idea Board", async (page) => {
  let element_0, element_1, element_3, element_4, element_2, element_6;
  await testmu.step("Navigate to http://localhost:5080/auth/login", { instructionId: "050bc484-26d6-403f-a05d-2ac6e26132e1" }, async () => {
    await page.goto("http://localhost:5080/auth/login");
  });
  
  await testmu.step("Waiting for the email and password fields to be filled", { instructionId: "58e4c364-4a67-4b92-84b6-d25974ea5534" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Type \"test@leantime.io\" into \"Email\"", { instructionId: "d570b315-7cf5-4716-b138-a6b456732474" }, async () => {
    element_0 = page.locator("internal:role=textbox[name=\"Email\"i]");
    await element_0.click();
    await element_0.fill("test@leantime.io");
  });
  
  await testmu.step("Type \"Test123456!\" into \"Password\"", { instructionId: "d6668727-49bd-4a16-aedc-decbc408f93e" }, async () => {
    element_1 = page.locator("internal:role=textbox[name=\"Password\"i]");
    await element_1.click();
    await element_1.fill("Test123456!");
  });
  
  await testmu.step("Pressing the Enter key to submit the Login form", { instructionId: "da61f3c1-184b-4c86-a107-0533dbd5c648" }, async () => {
    await page.keyboard.press("Enter");
  });
  
  await testmu.step("Waiting 1500 ms for the dashboard page to finish loading", { instructionId: "d077415f-f796-4432-841e-2c2870a2cb08" }, async () => {
    await page.waitForTimeout(1500);
  });
  
  await testmu.step("Reading the current browser URL", { instructionId: "536198c4-0d84-40b4-9c1d-fc284a660d42" }, async () => {
    setVar("dashboard_url_check", page.url());
  });
  
  await testmu.step("Assertion check", { instructionId: "cf59ea69-467c-4b5d-a7c5-a397d73c4aa4" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "The current browser URL contains \"/dashboard\".", "passed": true, "operator": "contains", "transforms": [], "json_path": null, "expected": "/dashboard", "extracted_value": "{{dashboard_url_check}}", "store_key": "dashboard_url_check", "variable_refs": {"{{dashboard_url_check}}": "http://localhost:5080/dashboard/home"}}], "sub_checks": [{"description": "The current browser URL contains \"/dashboard\".", "store_key": "dashboard_url_check", "expected_value": "/dashboard", "extracted_value": "{{dashboard_url_check}}", "operator": "contains", "transforms": []}], "composite_operator": "and", "claim": "the URL contains \"/dashboard\""});
  });
  
  await testmu.step("Navigate to http://localhost:5080/ideas/showBoards", { instructionId: "f2c16266-a6b6-434e-a2c2-0bf98d06d633" }, async () => {
    await page.goto("http://localhost:5080/ideas/showBoards");
  });
  
  await testmu.step("Waiting for the Ideas Board page to finish loading", { instructionId: "4e4b57e9-32d6-47ff-becc-33abe44ed3a8" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Checking the Welcome to the Idea Board dialog", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("welcome_to_the_idea_board_dialog_shown", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {86: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (el(86).textContent.trim() === 'Welcome to the Idea Board!');\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=heading[name=\"Welcome to the Idea Board!\"i]"], query: "whether the Welcome to the Idea Board dialog heading is present", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "A \"Welcome to the Idea Board!\" dialog is shown." }));
  });
  
  await testmu.step("Checking the Add Idea control", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("add_idea_button_visible", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {73: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (el(73).textContent.replace(/\\s+/g, ' ').trim().endsWith('Add Idea'));\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=link[name=\"\uf0eb Add Idea\"i]"], query: "whether the Add Idea control is present", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "The page shows an \"Add Idea\" button." }));
  });
  
  await testmu.step("Checking the Ideas / Board page heading", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("ideas_board_visible", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {62: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (el(62).textContent.replace(/\\s+/g, ' ').trim().includes('Ideas / Board'));\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=heading[name=\"Ideas Board\"i]"], query: "whether the Ideas / Board page heading is present", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "The page shows \"Ideas / Board\"." }));
  });
  
  if (await testmu.evaluateBranch([{"description": "A \"Welcome to the Idea Board!\" dialog is shown.", "store_key": "welcome_to_the_idea_board_dialog_shown", "expected_value": "true", "stored_value": "{{welcome_to_the_idea_board_dialog_shown}}", "operator": "equals", "transforms": []}], "and")) {
    await testmu.step("Clicking Close in the Welcome to the Idea Board dialog", { instructionId: "94841bed-d74d-4d12-a3e9-62b7ae9d8ab9" }, async () => {
      element_3 = page.locator("internal:role=link[name=\"\uf00d Close\"i]");
      await element_3.click();
    });
  } else {
    page = await testmu.executeKaneCli("do nothing", page);
  }
  
  await testmu.step("Assertion check", { instructionId: "ab5ec554-384b-43d6-bdb2-36ae69898601" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "The page shows \"Ideas / Board\".", "passed": true, "operator": "equals", "transforms": [], "json_path": null, "expected": "true", "extracted_value": "{{ideas_board_visible}}", "store_key": "ideas_board_visible", "variable_refs": {"{{ideas_board_visible}}": "true"}}, {"description": "The page shows an \"Add Idea\" button.", "passed": true, "operator": "equals", "transforms": [], "json_path": null, "expected": "true", "extracted_value": "{{add_idea_button_visible}}", "store_key": "add_idea_button_visible", "variable_refs": {"{{add_idea_button_visible}}": "true"}}], "sub_checks": [{"description": "The page shows \"Ideas / Board\".", "store_key": "ideas_board_visible", "expected_value": "true", "extracted_value": "{{ideas_board_visible}}", "operator": "equals", "transforms": []}, {"description": "The page shows an \"Add Idea\" button.", "store_key": "add_idea_button_visible", "expected_value": "true", "extracted_value": "{{add_idea_button_visible}}", "operator": "equals", "transforms": []}], "composite_operator": "and", "claim": "the page shows \"Ideas / Board\" and an \"Add Idea\" button."});
  });
  
  await testmu.step("Clicking Add Idea link in Ideas Board header", { instructionId: "b637ea92-45c1-44e7-aee0-a99e2ad8b160" }, async () => {
    element_4 = page.locator("internal:role=link[name=\"\uf0eb Add Idea\"i]");
    await element_4.click();
  });
  
  await testmu.step("Typing Short name for idea in dialog", { instructionId: "31d5538c-d6dc-47b4-9624-aa0ac30289dc" }, async () => {
    element_2 = page.locator("internal:role=textbox[name=\"Short name\"i]");
    await element_2.click();
    await element_2.fill("Dark mode for reports");
  });
  
  await testmu.step("Clicking Save & Close", { instructionId: "992dec38-510d-41a6-9155-39ea74af65e6" }, async () => {
    element_6 = page.locator("internal:role=button[name=\"Save & Close\"i]");
    await element_6.click();
  });
  
  await testmu.step("Waiting for the Ideas Board to reload for 1000 ms", { instructionId: "6fb8a132-a2fc-46fb-88d1-35aafa2c80c1" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Checking whether the Dark mode for reports idea card is shown", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("idea_card_dark_mode_for_reports_shown", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {77: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (el(77).textContent.trim() === 'Dark mode for reports');\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=heading[name=\"Dark mode for reports\"i]"], query: "whether the idea card titled Dark mode for reports is present on the board", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "An idea card titled \"Dark mode for reports\" is shown on the board." }));
  });
  
  await testmu.step("Assertion check", { instructionId: "525fc2e5-f4a2-42d8-8809-28f66955a017" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "An idea card titled \"Dark mode for reports\" is shown on the board.", "passed": true, "operator": "equals", "transforms": [], "json_path": null, "expected": "true", "extracted_value": "{{idea_card_dark_mode_for_reports_shown}}", "store_key": "idea_card_dark_mode_for_reports_shown", "variable_refs": {"{{idea_card_dark_mode_for_reports_shown}}": "true"}}], "sub_checks": [{"description": "An idea card titled \"Dark mode for reports\" is shown on the board.", "store_key": "idea_card_dark_mode_for_reports_shown", "expected_value": "true", "extracted_value": "{{idea_card_dark_mode_for_reports_shown}}", "operator": "equals", "transforms": []}], "composite_operator": "and", "claim": "an idea card titled \"Dark mode for reports\" is shown on the board"});
  });
});

testmu.run();