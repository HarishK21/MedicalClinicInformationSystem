<h2>Edit Patient: <?php echo htmlspecialchars($patient['FIRSTNAME'] . ' ' . $patient['LASTNAME']); ?></h2>
                        <form method="post" action="patient.php" class="crud-form">
                            <input type="hidden" name="record_action" value="edit_patient">
                            <input type="hidden" name="ohip_id" value="<?php echo $patient['OHIPID']; ?>">
                            
                            <div><label>OHIP ID:</label><input type="number" value="<?php echo $patient['OHIPID']; ?>" disabled></div>
                            <div><label>First Name:</label><input type="text" name="first_name" value="<?php echo htmlspecialchars($patient['FIRSTNAME']); ?>" required></div>
                            <div><label>Last Name:</label><input type="text" name="last_name" value="<?php echo htmlspecialchars($patient['LASTNAME']); ?>" required></div>
                            <div><label>Date of Birth:</label><input type="date" name="dob" value="<?php echo $dob_formatted; ?>" required></div>
                            <div><label>Sex:</label>
                                <select name="sex" required>
                                    <option value="Male" <?php if ($patient['SEX'] == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if ($patient['SEX'] == 'Female') echo 'selected'; ?>>Female</option>
                                </select>
                            </div>
                            <div><label>Height (cm):</label><input type="number" name="height" value="<?php echo $patient['HEIGHT']; ?>"></div>
                            <div><label>Weight (kg):</label><input type="number" name="weight" value="<?php echo $patient['WEIGHT']; ?>"></div>
                            <div><label>Email:</label><input type="email" name="email" value="<?php echo htmlspecialchars($patient['EMAIL']); ?>"></div>
                            <div><label>Phone:</label><input type="text" name="phone" value="<?php echo htmlspecialchars($patient['PHONE']); ?>"></div>
                            <div><label>Address:</label><input type="text" name="address" value="<?php echo htmlspecialchars($patient['ADDRESS']); ?>"></div>

                            <div class="form-actions">
                                <button type="submit" class="sidebar-btn primary">Save Changes</button>
                                <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                            </div>
                        </form>