<div id="userCreationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center; z-index: 9999;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; font-family: system-ui, -apple-system, sans-serif;">
        <!-- Modal Header Layout -->
        <div style="padding: 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: #0f172a;">Account Provisioning Form Wizard</h4>
            <button onclick="document.getElementById('userCreationModal').style.display='none';" style="background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <!-- Modal Form Body Context -->
        <form method="POST" style="margin: 0; padding: 20px;">
            <input type="hidden" name="create_user_action" value="1">

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">First Name *</label>
                    <input type="text" name="first_name" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Last Name *</label>
                    <input type="text" name="last_name" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Network Email Address *</label>
                <input type="email" name="email" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">System Username *</label>
                <input type="text" name="username" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Access Password *</label>
                <input type="password" name="password" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">System Access Authorization Role *</label>
                <select name="role" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-size: 0.9rem;">
                    <option value="Cashier">Cashier</option>
                    <option value="Custodian">Custodian</option>
                    <option value="Manager">Manager</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>

            <!-- Form Action Triggers -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <button type="button" onclick="document.getElementById('userCreationModal').style.display='none';" style="background: white; color: #475569; border: 1px solid #cbd5e1; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="background: #2563eb; color: white; border: none; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 4px; cursor: pointer;">Execute Provisioning Process</button>
            </div>
        </form>
    </div>
</div>